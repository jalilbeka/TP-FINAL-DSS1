<?php
/**
 * AI Support Chatbot API Endpoint
 *
 * Receives user messages and current-session conversation history,
 * validates input, enforces rate limits, and forwards requests to
 * Google Gemini server-side.
 *
 * Endpoint:
 *   POST api/chat.php
 *   Body: {
 *     "message": "How does WSM work?",
 *     "history": [ { "role": "user"|"model", "text": "..." } ],
 *     "context": { "lang": "en"|"fr", "tab": "dashboard" }
 *   }
 *
 * @package DSS University
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/gemini.php';

// Same-origin JSON security headers (no wildcard CORS)
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed. Use POST.', 405);
}

const CHAT_MAX_RAW_BODY_BYTES      = 32768;
const CHAT_MAX_MESSAGE_LENGTH      = 1000;
const CHAT_MAX_HISTORY_TURNS       = 10;
const CHAT_MAX_HISTORY_ITEM_LENGTH = 1500;
const CHAT_COOLDOWN_SECONDS        = 3;
const CHAT_IP_MAX_PER_MINUTE       = 20;

/**
 * Enforce per-IP cooldown and per-minute cap using a temp-dir lock file.
 * Prevents bypassing session cooldown by omitting cookies.
 *
 * @param string $ip Client IP address
 * @return array{allowed: bool, retry_after: int, message: string}
 */
function checkIpRateLimit(string $ip): array
{
    $safeKey = hash('sha256', 'dss_chat_rl_' . $ip);
    $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dss_university_rl';
    if (!is_dir($dir)) {
        @mkdir($dir, 0700, true);
    }

    $file = $dir . DIRECTORY_SEPARATOR . $safeKey . '.json';
    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        // If temp dir is unwritable, fall back to session cooldown only
        return ['allowed' => true, 'retry_after' => 0, 'message' => ''];
    }

    $now = microtime(true);
    $result = ['allowed' => true, 'retry_after' => 0, 'message' => ''];

    if (@flock($fp, LOCK_EX)) {
        $raw = stream_get_contents($fp);
        $state = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($state)) {
            $state = ['last' => 0.0, 'window' => []];
        }

        $last = (float) ($state['last'] ?? 0.0);
        $window = array_values(array_filter(
            is_array($state['window'] ?? null) ? $state['window'] : [],
            static fn($ts): bool => is_numeric($ts) && ($now - (float) $ts) < 60.0
        ));

        if ($last > 0.0 && ($now - $last) < CHAT_COOLDOWN_SECONDS) {
            $wait = max(1, (int) ceil(CHAT_COOLDOWN_SECONDS - ($now - $last)));
            $result = [
                'allowed'     => false,
                'retry_after' => $wait,
                'message'     => sprintf('Please wait %d second(s) before sending another message.', $wait),
            ];
        } elseif (count($window) >= CHAT_IP_MAX_PER_MINUTE) {
            $oldest = (float) $window[0];
            $wait = max(1, (int) ceil(60.0 - ($now - $oldest)));
            $result = [
                'allowed'     => false,
                'retry_after' => $wait,
                'message'     => 'Too many requests. Please wait a moment and try again.',
            ];
        } else {
            $state['last'] = $now;
            $window[] = $now;
            $state['window'] = $window;

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string) json_encode($state));
            fflush($fp);
        }

        @flock($fp, LOCK_UN);
    }

    @fclose($fp);
    return $result;
}

try {
    startSession();

    // Read and strictly validate JSON request body
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || strlen($rawBody) > CHAT_MAX_RAW_BODY_BYTES) {
        jsonResponse(false, null, 'Request payload is too large.', 400);
    }

    if (trim($rawBody) === '') {
        jsonResponse(false, null, 'Request body must be valid JSON.', 400);
    }

    $input = json_decode($rawBody, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
        jsonResponse(false, null, 'Malformed JSON payload.', 400);
    }

    $rawMessage = $input['message'] ?? '';
    if (!is_string($rawMessage)) {
        jsonResponse(false, null, 'Message must be a text string.', 400);
    }

    $message = trim($rawMessage);
    if ($message === '') {
        jsonResponse(false, null, 'Please enter a message before sending.', 400);
    }

    $messageLen = function_exists('mb_strlen') ? mb_strlen($message, 'UTF-8') : strlen($message);
    if ($messageLen > CHAT_MAX_MESSAGE_LENGTH) {
        jsonResponse(
            false,
            null,
            sprintf('Message is too long (maximum %d characters).', CHAT_MAX_MESSAGE_LENGTH),
            400
        );
    }

    // Per-session cooldown
    $now = microtime(true);
    $lastRequestAt = isset($_SESSION['chat_last_request_at']) ? (float) $_SESSION['chat_last_request_at'] : 0.0;
    $elapsed = $now - $lastRequestAt;
    if ($lastRequestAt > 0.0 && $elapsed < CHAT_COOLDOWN_SECONDS) {
        $waitSeconds = max(1, (int) ceil(CHAT_COOLDOWN_SECONDS - $elapsed));
        header('Retry-After: ' . $waitSeconds);
        jsonResponse(
            false,
            null,
            sprintf('Please wait %d second(s) before sending another message.', $waitSeconds),
            429
        );
    }

    // Per-IP cooldown and per-minute rate limit (cookie-independent)
    $clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $ipCheck = checkIpRateLimit($clientIp);
    if (!$ipCheck['allowed']) {
        header('Retry-After: ' . $ipCheck['retry_after']);
        jsonResponse(false, null, $ipCheck['message'], 429);
    }

    $_SESSION['chat_last_request_at'] = $now;

    // Validate and sanitize conversation history from current session
    $rawHistory = $input['history'] ?? [];
    $history = [];

    if (is_array($rawHistory)) {
        $slicedHistory = array_slice($rawHistory, -CHAT_MAX_HISTORY_TURNS);
        foreach ($slicedHistory as $item) {
            if (!is_array($item)) {
                continue;
            }
            $role = $item['role'] ?? '';
            $text = $item['text'] ?? '';
            if (!is_string($role) || !is_string($text)) {
                continue;
            }

            $normalizedRole = ($role === 'model' || $role === 'assistant')
                ? 'model'
                : ($role === 'user' ? 'user' : '');
            $trimmedText = trim($text);

            if ($normalizedRole === '' || $trimmedText === '') {
                continue;
            }

            $trimmedText = function_exists('mb_substr')
                ? mb_substr($trimmedText, 0, CHAT_MAX_HISTORY_ITEM_LENGTH, 'UTF-8')
                : substr($trimmedText, 0, CHAT_MAX_HISTORY_ITEM_LENGTH);

            $history[] = [
                'role' => $normalizedRole,
                'text' => $trimmedText,
            ];
        }
    }

    // Sanitize UI context
    $rawContext = $input['context'] ?? [];
    $allowedTabs = ['auth', 'dashboard', 'students', 'specializations', 'criteria', 'evaluations', 'results'];
    $lang = 'en';
    $tab = 'unknown';

    if (is_array($rawContext)) {
        if (($rawContext['lang'] ?? '') === 'fr') {
            $lang = 'fr';
        }
        $candidateTab = $rawContext['tab'] ?? '';
        if (is_string($candidateTab) && in_array($candidateTab, $allowedTabs, true)) {
            $tab = $candidateTab;
        }
    }

    $sessionUser = isAuthenticated() ? getCurrentUser() : null;

    $result = callGeminiChat(
        $message,
        $history,
        ['lang' => $lang, 'tab' => $tab],
        $sessionUser
    );

    if (!$result['ok']) {
        jsonResponse(false, null, $result['error'], $result['http_code']);
    }

    jsonResponse(true, ['reply' => $result['reply']], 'Reply generated.');
} catch (Throwable $e) {
    jsonResponse(false, null, 'An unexpected error occurred. Please try again later.', 500);
}
