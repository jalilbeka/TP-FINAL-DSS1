<?php
/**
 * Google Gemini API Client & Website Support Prompt Builder
 *
 * Modular server-side integration with the Google Gemini REST API.
 * Keeps API credentials strictly on the server and grounds responses
 * in the actual DSS University website structure and data.
 *
 * @package DSS University
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/env.php';

/**
 * Default Gemini model used when GEMINI_MODEL is not configured.
 * (gemini-2.5-flash-lite returns 404 for new API keys; gemini-3.5-flash-lite is its direct replacement.)
 */
const DEFAULT_GEMINI_MODEL = 'gemini-3.5-flash-lite';

/**
 * Fetch public catalog context (specializations & criteria) from MySQL.
 * Falls back gracefully if the database is unreachable.
 *
 * @return array{specializations: array<int, array<string, mixed>>, criteria: array<int, array<string, mixed>>}
 */
function getPublicWebsiteContext(): array
{
    $context = [
        'specializations' => [],
        'criteria'        => [],
    ];

    try {
        $pdo = Database::getConnection();

        $specStmt = $pdo->query(
            "SELECT code, name_en, name_fr, description_en, description_fr
             FROM specializations
             ORDER BY code"
        );
        if ($specStmt !== false) {
            $context['specializations'] = $specStmt->fetchAll();
        }

        $critStmt = $pdo->query(
            "SELECT name_en, name_fr, type, weight
             FROM criteria
             ORDER BY id"
        );
        if ($critStmt !== false) {
            $context['criteria'] = $critStmt->fetchAll();
        }
    } catch (Throwable $e) {
        // Fallback: keep empty arrays so chatbot still works with baseline site overview
    }

    return $context;
}

/**
 * Build the system instruction prompt describing the website, its features,
 * navigation, and live catalog data.
 *
 * @param array      $clientContext Optional sanitized UI context ['lang' => 'en'|'fr', 'tab' => string]
 * @param array|null $sessionUser   Optional authenticated session user info
 * @return string
 */
function buildChatbotSystemPrompt(array $clientContext = [], ?array $sessionUser = null): string
{
    $lang = ($clientContext['lang'] ?? 'en') === 'fr' ? 'French' : 'English';
    $screen = $clientContext['tab'] ?? 'unknown';

    $roleText = 'Visitor (not logged in)';
    if ($sessionUser !== null && !empty($sessionUser['user_id'])) {
        $roleText = $sessionUser['role'] === 'admin' ? 'Admin' : 'Student';
    }

    $dbContext = getPublicWebsiteContext();

    $specLines = [];
    foreach ($dbContext['specializations'] as $spec) {
        $specLines[] = sprintf(
            '%s: %s (%s)',
            $spec['code'],
            $spec['name_en'],
            $spec['description_en']
        );
    }
    $specsText = !empty($specLines)
        ? implode('; ', $specLines)
        : 'P1: AI; P2: Cybersecurity; P3: Data Science; P4: ISSE';

    $critLines = [];
    foreach ($dbContext['criteria'] as $crit) {
        $critLines[] = sprintf(
            '%s (%s, w=%.2f)',
            $crit['name_en'],
            $crit['type'],
            (float) $crit['weight']
        );
    }
    $criteriaText = !empty($critLines)
        ? implode('; ', $critLines)
        : 'Programming Skills, Mathematics, Competency Alignment, Career Opportunities, Personal Interest (maximize); Difficulty (minimize)';

    return <<<PROMPT
You are the concise, friendly support assistant for DSS University (Univ. 8 Mai 1945 Guelma, CS Dept, 3rd Year ISIL) — a web app ranking university specializations via the Weighted Sum Model (WSM).

Site structure:
- Auth screen: Login or Register (new Student account), EN|FR toggle.
- Tabs after login: Dashboard (stats), Students (Admin CRUD), Specializations (P1-P4 info), Criteria (maximize/minimize & weights summing to 1.00), Evaluations (1-10 score matrix + Save/Preview), Results (WSM rank, tie alert, Bar & Radar charts).
- Header: EN|FR language toggle, Dark/Light theme toggle, Logout.
- WSM math: maximize = score/max; minimize = min/score; overall = sum(weight * normalized).

Specializations: {$specsText}
Criteria: {$criteriaText}
User context: UI={$lang}, Role={$roleText}, Screen={$screen}.

Rules: Reply in the user's language (default {$lang}). Be brief and clear. Only use facts above; if unknown (e.g., private grades or external policies), say so politely.
PROMPT;
}

/**
 * Send a chat turn + session history to Google Gemini API.
 *
 * @param string     $userMessage   Current validated user message
 * @param array      $history       Sanitized prior turns: [['role' => 'user'|'model', 'text' => string], ...]
 * @param array      $clientContext Sanitized UI context
 * @param array|null $sessionUser   Current logged-in user or null
 * @return array{ok: bool, reply: string, error: string, http_code: int}
 */
function callGeminiChat(
    string $userMessage,
    array $history = [],
    array $clientContext = [],
    ?array $sessionUser = null
): array {
    $apiKey = getEnvConfig('GEMINI_API_KEY');
    if ($apiKey === null || $apiKey === '' || $apiKey === 'your_api_key_here') {
        return [
            'ok'        => false,
            'reply'     => '',
            'error'     => 'Gemini API key is not configured on the server. Please set GEMINI_API_KEY in your .env file.',
            'http_code' => 503,
        ];
    }

    $model = getEnvConfig('GEMINI_MODEL', DEFAULT_GEMINI_MODEL) ?: DEFAULT_GEMINI_MODEL;
    // Allow only safe model identifier characters
    $model = preg_replace('/[^a-zA-Z0-9._-]/', '', $model) ?: DEFAULT_GEMINI_MODEL;

    $endpoint = sprintf(
        'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
        rawurlencode($model)
    );

    // Build Gemini contents array from session history + current message
    $contents = [];
    foreach ($history as $turn) {
        $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
        $text = trim((string) ($turn['text'] ?? ''));
        if ($text !== '') {
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $text]],
            ];
        }
    }

    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' => $userMessage]],
    ];

    $payload = [
        'system_instruction' => [
            'parts' => [
                ['text' => buildChatbotSystemPrompt($clientContext, $sessionUser)],
            ],
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature'     => 0.4,
            'maxOutputTokens' => 300,
        ],
    ];

    $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
    if ($jsonPayload === false) {
        return [
            'ok'        => false,
            'reply'     => '',
            'error'     => 'Failed to encode request payload.',
            'http_code' => 500,
        ];
    }

    $headers = [
        'Content-Type: application/json',
        'X-goog-api-key: ' . $apiKey,
    ];

    $httpResult = sendJsonPostRequest($endpoint, $headers, $jsonPayload);
    if (!$httpResult['ok']) {
        return [
            'ok'        => false,
            'reply'     => '',
            'error'     => $httpResult['error'],
            'http_code' => 502,
        ];
    }

    $statusCode  = $httpResult['status'];
    $rawResponse = $httpResult['body'];
    $decoded     = json_decode($rawResponse, true);

    if ($statusCode < 200 || $statusCode >= 300) {
        $apiMsg = $decoded['error']['message'] ?? '';
        $friendlyError = mapGeminiHttpError($statusCode, is_string($apiMsg) ? $apiMsg : '');
        return [
            'ok'        => false,
            'reply'     => '',
            'error'     => $friendlyError,
            'http_code' => $statusCode >= 500 ? 502 : ($statusCode === 429 ? 429 : 502),
        ];
    }

    $parts = $decoded['candidates'][0]['content']['parts'] ?? [];
    $replyParts = [];
    if (is_array($parts)) {
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text'])) {
                $replyParts[] = $part['text'];
            }
        }
    }

    $reply = trim(implode("\n", $replyParts));
    if ($reply === '') {
        $finishReason = $decoded['candidates'][0]['finishReason'] ?? '';
        $msg = $finishReason === 'SAFETY'
            ? 'I cannot respond to that request due to safety filters. Please rephrase your question.'
            : 'The AI service returned an empty response. Please try again.';

        return [
            'ok'        => false,
            'reply'     => '',
            'error'     => $msg,
            'http_code' => 502,
        ];
    }

    return [
        'ok'        => true,
        'reply'     => $reply,
        'error'     => '',
        'http_code' => 200,
    ];
}

/**
 * Execute an HTTPS JSON POST request using cURL when available, or PHP's
 * native HTTP/OpenSSL stream wrapper as a zero-extension fallback (e.g. XAMPP Apache).
 *
 * @param string             $url
 * @param array<int, string> $headers
 * @param string             $jsonPayload
 * @return array{ok: bool, status: int, body: string, error: string}
 */
function sendJsonPostRequest(string $url, array $headers, string $jsonPayload): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 25,
        ]);

        $rawResponse = curl_exec($ch);
        $curlErrno   = curl_errno($ch);
        $statusCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno !== 0 || $rawResponse === false) {
            return [
                'ok'     => false,
                'status' => 0,
                'body'   => '',
                'error'  => 'Could not connect to the AI service. Please check your internet connection and try again.',
            ];
        }

        return [
            'ok'     => true,
            'status' => $statusCode,
            'body'   => (string) $rawResponse,
            'error'  => '',
        ];
    }

    if (!in_array('https', stream_get_wrappers(), true)) {
        return [
            'ok'     => false,
            'status' => 0,
            'body'   => '',
            'error'  => 'PHP OpenSSL or cURL support is required to reach the Gemini API.',
        ];
    }

    $sslOptions = [
        'verify_peer'      => true,
        'verify_peer_name' => true,
    ];
    $caFile = ini_get('openssl.cafile') ?: ini_get('curl.cainfo');
    if (is_string($caFile) && $caFile !== '' && is_file($caFile)) {
        $sslOptions['cafile'] = $caFile;
    }

    $context = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => implode("\r\n", $headers) . "\r\n",
            'content'       => $jsonPayload,
            'timeout'       => 25,
            'ignore_errors' => true,
        ],
        'ssl' => $sslOptions,
    ]);

    $rawResponse = @file_get_contents($url, false, $context);
    if ($rawResponse === false) {
        return [
            'ok'     => false,
            'status' => 0,
            'body'   => '',
            'error'  => 'Could not connect to the AI service. Please check your internet connection and try again.',
        ];
    }

    $statusCode = 0;
    $responseHeaders = function_exists('http_get_last_response_headers')
        ? http_get_last_response_headers()
        : ($http_response_header ?? []);

    if (is_array($responseHeaders)) {
        foreach ($responseHeaders as $line) {
            if (preg_match('/^HTTP\/\S+\s+(\d{3})/i', $line, $matches)) {
                $statusCode = (int) $matches[1];
            }
        }
    }

    return [
        'ok'     => true,
        'status' => $statusCode,
        'body'   => (string) $rawResponse,
        'error'  => '',
    ];
}

/**
 * Convert upstream Gemini HTTP errors into safe, actionable user messages.
 *
 * @param int    $statusCode Upstream HTTP status code
 * @param string $apiMessage Upstream error message (never exposed verbatim if it could leak internals)
 * @return string
 */
function mapGeminiHttpError(int $statusCode, string $apiMessage): string
{
    if ($statusCode === 400 && stripos($apiMessage, 'API key') !== false) {
        return 'The configured Gemini API key appears invalid. Please verify GEMINI_API_KEY in your .env file.';
    }
    if ($statusCode === 401 || $statusCode === 403) {
        return 'AI service authentication failed. Please check your GEMINI_API_KEY permissions.';
    }
    if ($statusCode === 429) {
        return 'The AI assistant is receiving too many requests right now. Please wait a moment and try again.';
    }
    if ($statusCode === 404) {
        return 'The configured Gemini model could not be found. Please check GEMINI_MODEL.';
    }
    return 'The AI service is temporarily unavailable. Please try again shortly.';
}
