<?php
/**
 * Environment Variable Loader
 *
 * Reads configuration variables from process environment first,
 * then falls back to a local `.env` file in the project root.
 *
 * @package DSS University
 */

declare(strict_types=1);

/**
 * Parse key=value pairs from the project's `.env` file if present.
 *
 * @return array<string, string>
 */
function loadDotEnv(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $cached = [];
    $envPath = dirname(__DIR__) . '/.env';

    if (!is_file($envPath) || !is_readable($envPath)) {
        return $cached;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return $cached;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        if (str_starts_with($trimmed, 'export ')) {
            $trimmed = trim(substr($trimmed, 7));
        }

        $eqPos = strpos($trimmed, '=');
        if ($eqPos === false) {
            continue;
        }

        $key = trim(substr($trimmed, 0, $eqPos));
        $val = trim(substr($trimmed, $eqPos + 1));

        if ($key === '') {
            continue;
        }

        // Strip matching surrounding single or double quotes
        $len = strlen($val);
        if ($len >= 2) {
            $first = $val[0];
            $last = $val[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $val = substr($val, 1, -1);
            }
        }

        $cached[$key] = $val;
    }

    return $cached;
}

/**
 * Get an environment variable from OS/server env or `.env`.
 *
 * @param string      $key     Variable name
 * @param string|null $default Fallback value if not defined or empty
 * @return string|null
 */
function getEnvConfig(string $key, ?string $default = null): ?string
{
    $candidates = [
        getenv($key),
        $_ENV[$key] ?? null,
        $_SERVER[$key] ?? null,
    ];

    foreach ($candidates as $value) {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
    }

    $dotEnv = loadDotEnv();
    if (isset($dotEnv[$key]) && trim($dotEnv[$key]) !== '') {
        return trim($dotEnv[$key]);
    }

    return $default;
}
