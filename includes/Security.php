<?php
/**
 * DARK MATTER — Elite Fitness
 * Security, CSRF Protection, Anti-Spam & Validation Layer
 */

declare(strict_types=1);

namespace DarkMatter;

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/helpers.php';

class Security
{
    /**
     * Determine if connection is encrypted over HTTPS (including cloud reverse proxies).
     */
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') {
            return true;
        }
        return (string) Env::get('APP_ENV') === 'production';
    }

    /**
     * Dispatch production security headers.
     */
    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), camera=(), microphone=()');

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Start secure session if not already active.
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                self::sendSecurityHeaders();

                $cookieParams = [
                    'lifetime' => 0,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => self::isHttps(),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ];
                session_set_cookie_params($cookieParams);
                ini_set('session.use_strict_mode', '1');
            }
            session_start();
        }
    }

    /**
     * Generate or return existing CSRF token for the active session.
     */
    public static function getCsrfToken(): string
    {
        self::initSession();

        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Generate HTML hidden input for CSRF token.
     */
    public static function csrfField(): string
    {
        $token = self::getCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify a submitted CSRF token against active session token.
     */
    public static function verifyCsrfToken(?string $token): bool
    {
        self::initSession();

        if (empty($token) || empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Honeypot Anti-Spam Check:
     * Hidden field that legitimate users do not fill, but automated spambots do.
     */
    public static function verifyHoneypot(array $input): bool
    {
        $honeypotKey = (string) Env::get('HONEYPOT_FIELD', 'dm_telemetry_check');

        if (isset($input[$honeypotKey]) && trim((string) $input[$honeypotKey]) !== '') {
            error_log('[DARK MATTER ANTI-SPAM]: Honeypot triggered by IP: ' . self::getClientIp());
            return false;
        }

        return true;
    }

    /**
     * Sliding Window Rate Limiting based on IP and action.
     * Prevents automated endpoint spamming.
     */
    public static function checkRateLimit(string $action, ?int $maxAttempts = null, ?int $windowSeconds = null): bool
    {
        self::initSession();

        $ip = self::getClientIp();
        $isLocalhost = in_array($ip, ['127.0.0.1', '::1', 'localhost'], true);
        $isDev = (string) Env::get('APP_ENV', 'development') === 'development';

        // In local development on localhost, allow higher attempts so developers are not locked out
        if ($isDev && $isLocalhost) {
            $maxAttempts = 50;
        } else {
            $maxAttempts = $maxAttempts ?? (int) Env::get('RATE_LIMIT_PER_MINUTE', 10);
        }

        $windowSeconds = $windowSeconds ?? (int) Env::get('RATE_LIMIT_WINDOW_SECONDS', 300);

        $rateKey = "rate_limit_{$action}_{$ip}";

        $now = time();
        $history = $_SESSION[$rateKey] ?? [];

        // Filter out timestamps older than the window
        $history = array_filter($history, fn($timestamp) => ($now - $timestamp) < $windowSeconds);

        if (count($history) >= $maxAttempts) {
            error_log("[DARK MATTER RATE LIMIT]: Exceeded {$maxAttempts} attempts for {$action} by {$ip}");
            return false;
        }

        $history[] = $now;
        $_SESSION[$rateKey] = $history;
        return true;
    }

    /**
     * Clear rate limit history for a specific action and IP.
     */
    public static function clearRateLimit(string $action): void
    {
        self::initSession();
        $ip = self::getClientIp();
        $rateKey = "rate_limit_{$action}_{$ip}";
        unset($_SESSION[$rateKey]);
    }

    /**
     * Sanitize string and enforce maximum length.
     */
    public static function sanitizeString(mixed $input, int $maxLength = 255): string
    {
        if (!is_string($input)) {
            return '';
        }
        $clean = trim(strip_tags($input));
        return mb_substr($clean, 0, $maxLength, 'UTF-8');
    }

    /**
     * Validate and normalize email address.
     */
    public static function validateEmail(mixed $email): ?string
    {
        if (!is_string($email)) {
            return null;
        }
        $email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 150) {
            return strtolower($email);
        }
        return null;
    }

    /**
     * Validate phone number (allows international format, spaces, dashes, parentheses).
     */
    public static function validatePhone(mixed $phone): ?string
    {
        if (!is_string($phone)) {
            return null;
        }
        $phone = trim($phone);
        // Minimum 7 digits, maximum 25 characters including formatting
        if (preg_match('/^[0-9+\s\-()]{7,25}$/', $phone)) {
            return $phone;
        }
        return null;
    }

    /**
     * Validate date string (YYYY-MM-DD).
     */
    public static function validateDate(mixed $date): ?string
    {
        if (!is_string($date) || trim($date) === '') {
            return null;
        }
        $d = \DateTime::createFromFormat('Y-m-d', trim($date));
        if ($d && $d->format('Y-m-d') === trim($date)) {
            return trim($date);
        }
        return null;
    }

    /**
     * Validate that an input is within an allowed array of values.
     */
    public static function validateEnum(mixed $value, array $allowed): ?string
    {
        if (is_string($value) && in_array($value, $allowed, true)) {
            return $value;
        }
        return null;
    }

    /**
     * Safely resolve client IP address.
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', (string) $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }
}

