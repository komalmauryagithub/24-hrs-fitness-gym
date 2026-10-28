<?php
/**
 * DARK MATTER — Elite Fitness
 * Environment Variable Loader (Zero Dependencies)
 */

declare(strict_types=1);

namespace DarkMatter;

class Env
{
    private static bool $loaded = false;
    private static array $variables = [];

    /**
     * Load environment variables from a .env file into memory and environment.
     */
    public static function load(string $filePath): void
    {
        if (self::$loaded && !empty(self::$variables)) {
            return;
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            // If .env is missing, attempt to read from system environment
            self::$loaded = true;
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }

            // Find first '=' delimiter
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $name = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // Strip surrounding quotes if present
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            self::$variables[$name] = $value;
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv("{$name}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Get an environment variable with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            $defaultEnvPath = dirname(__DIR__) . '/.env';
            self::load($defaultEnvPath);
        }

        if (array_key_exists($key, self::$variables)) {
            return self::castValue(self::$variables[$key]);
        }

        $envVal = getenv($key);
        if ($envVal !== false) {
            return self::castValue($envVal);
        }

        if (isset($_ENV[$key])) {
            return self::castValue($_ENV[$key]);
        }

        return $default;
    }

    /**
     * Cast boolean, null, and numeric string literals.
     */
    private static function castValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $lower = strtolower($value);
        return match ($lower) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => is_numeric($value) && !str_starts_with($value, '0') ? (str_contains($value, '.') ? (float) $value : (int) $value) : $value,
        };
    }
}
