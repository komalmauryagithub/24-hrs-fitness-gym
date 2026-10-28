<?php
/**
 * DARK MATTER — Elite Fitness
 * Production Health & Telemetry Liveness Endpoint
 *
 * Designed for Docker, Kubernetes, Render, Railway & Cloud Load Balancers.
 * Does NOT expose secrets, credentials, or internal topology.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

ini_set('display_errors', '0');

require_once __DIR__ . '/includes/Env.php';
require_once __DIR__ . '/includes/Database.php';

use DarkMatter\Database;
use DarkMatter\Env;

$dbConnected = Database::ping();

// Storage check
$uploadDir = __DIR__ . '/uploads';
$storageWritable = is_dir($uploadDir) && is_writable($uploadDir);

$isHealthy = $dbConnected;

$response = [
    'status'    => $isHealthy ? 'healthy' : 'degraded',
    'platform'  => 'DARK MATTER — Elite Fitness',
    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'telemetry' => [
        'database' => $dbConnected ? 'operational' : 'offline',
        'storage'  => $storageWritable ? 'writable' : 'restricted',
        'php'      => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION
    ]
];

if (!$isHealthy) {
    http_response_code(503);
} else {
    http_response_code(200);
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
exit;
