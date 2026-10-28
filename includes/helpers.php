<?php
/**
 * DARK MATTER — Elite Fitness
 * Global Helper Utilities
 */

declare(strict_types=1);

if (!function_exists('e')) {
    /**
     * Safely escape output for HTML context (prevents XSS).
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
