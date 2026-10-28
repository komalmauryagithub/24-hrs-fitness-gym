<?php
/**
 * DARK MATTER — Elite Fitness
 * CSRF Token Fetcher Endpoint
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
ini_set('display_errors', '0');

require_once __DIR__ . '/../includes/Security.php';

use DarkMatter\Security;

Security::initSession();

echo json_encode([
    'success'    => true,
    'csrf_token' => Security::getCsrfToken()
]);
