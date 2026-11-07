<?php
/**
 * Shared Helper Functions
 *
 * Provides common utilities for all API endpoints:
 * - Session management and authentication checks
 * - Role-based access control
 * - JSON response formatting
 * - CORS header configuration
 *
 * @package DSS University
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// CORS Headers
// ---------------------------------------------------------------------------

/**
 * Set CORS and JSON content-type headers for API responses.
 *
 * @return void
 */
function setCorsHeaders(): void
{
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');

    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ---------------------------------------------------------------------------
// Session Management
// ---------------------------------------------------------------------------

/**
 * Start session if not already active.
 *
 * @return void
 */
function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if the current user is authenticated.
 *
 * @return bool True if user is logged in
 */
function isAuthenticated(): bool
{
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication. Sends 401 response and exits if not logged in.
 *
 * @return void
 */
function requireAuth(): void
{
    if (!isAuthenticated()) {
        jsonResponse(false, null, 'Authentication required. Please log in.', 401);
    }
}

/**
 * Get the currently authenticated user's data from the session.
 *
 * @return array{user_id: int, username: string, role: string, student_id: int|null}
 */
function getCurrentUser(): array
{
    return [
        'user_id'    => (int) ($_SESSION['user_id'] ?? 0),
        'username'   => $_SESSION['username'] ?? '',
        'role'       => $_SESSION['role'] ?? '',
        'student_id' => isset($_SESSION['student_id']) ? (int) $_SESSION['student_id'] : null,
    ];
}

// ---------------------------------------------------------------------------
// Role Checks
// ---------------------------------------------------------------------------

/**
 * Check if the current user has the admin role.
 *
 * @return bool True if user is an admin
 */
function isAdmin(): bool
{
    return isAuthenticated() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Require admin role. Sends 403 response and exits if not admin.
 *
 * @return void
 */
function requireAdmin(): void
{
    requireAuth();
    if (!isAdmin()) {
        jsonResponse(false, null, 'Access denied. Admin privileges required.', 403);
    }
}

// ---------------------------------------------------------------------------
// JSON Response
// ---------------------------------------------------------------------------

/**
 * Send a standardized JSON response and exit.
 *
 * @param bool        $success  Whether the operation succeeded
 * @param mixed       $data     Response data payload
 * @param string      $message  Human-readable message
 * @param int         $httpCode HTTP status code (default 200)
 * @return never
 */
function jsonResponse(bool $success, mixed $data = null, string $message = '', int $httpCode = 200): never
{
    http_response_code($httpCode);

    $response = [
        'success' => $success,
        'message' => $message,
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ---------------------------------------------------------------------------
// Input Helpers
// ---------------------------------------------------------------------------

/**
 * Get the request body parsed as JSON.
 *
 * @return array Decoded JSON data
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return $_POST;
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

/**
 * Get a value from the input data with an optional default.
 *
 * @param array  $data    Input data array
 * @param string $key     Key to look up
 * @param mixed  $default Default value if key is missing
 * @return mixed
 */
function getInput(array $data, string $key, mixed $default = null): mixed
{
    return $data[$key] ?? $_POST[$key] ?? $_GET[$key] ?? $default;
}
