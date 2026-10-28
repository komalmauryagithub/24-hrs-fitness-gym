<?php
/**
 * DARK MATTER — Elite Fitness
 * CLI Admin Account Generator & Password Reset Utility
 *
 * Usage (CLI):
 *   php scripts/create_admin.php
 *   OR
 *   php scripts/create_admin.php <username> <email> <password> [role]
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access Denied: This utility must be executed from the CLI command prompt.\n");
}

require_once __DIR__ . '/../includes/Env.php';
require_once __DIR__ . '/../includes/Database.php';

use DarkMatter\Database;

echo "\n=======================================================\n";
echo "  DARK MATTER — SECURE ADMIN PROVISIONING TELEMETRY   \n";
echo "=======================================================\n\n";

$args = array_slice($argv, 1);

if (count($args) >= 3) {
    $username = trim($args[0]);
    $email    = trim($args[1]);
    $password = $args[2];
    $role     = $args[3] ?? 'superadmin';
} else {
    // Interactive prompt
    echo "Enter Admin Username (min 3 chars): ";
    $username = trim((string) fgets(STDIN));

    echo "Enter Admin Email Address: ";
    $email = trim((string) fgets(STDIN));

    echo "Enter Admin Secure Password (min 8 chars): ";
    $password = trim((string) fgets(STDIN));

    echo "Enter Admin Role [superadmin / manager] (default: superadmin): ";
    $roleInput = trim((string) fgets(STDIN));
    $role = $roleInput !== '' ? $roleInput : 'superadmin';
}

if (strlen($username) < 3) {
    die("Error: Username must contain at least 3 characters.\n");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Error: Invalid email format provided.\n");
}

if (strlen($password) < 8) {
    die("Error: Password must contain at least 8 characters.\n");
}

try {
    $existing = Database::fetchOne(
        "SELECT `id`, `username` FROM `admins` WHERE `username` = :u OR `email` = :e",
        [':u' => $username, ':e' => $email]
    );

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    if ($existing) {
        Database::execute(
            "UPDATE `admins` 
             SET `password_hash` = :h, `role` = :r, `email` = :e, `updated_at` = NOW() 
             WHERE `id` = :id",
            [
                ':h'  => $hash,
                ':r'  => $role,
                ':e'  => $email,
                ':id' => $existing['id']
            ]
        );
        echo "\n[SUCCESS] Existing admin record '{$username}' updated successfully.\n";
    } else {
        Database::execute(
            "INSERT INTO `admins` (`username`, `email`, `password_hash`, `role`, `created_at`)
             VALUES (:u, :e, :h, :r, NOW())",
            [
                ':u' => $username,
                ':e' => $email,
                ':h' => $hash,
                ':r' => $role
            ]
        );
        $id = Database::lastInsertId();
        echo "\n[SUCCESS] New Admin ID #{$id} ('{$username}') provisioned successfully.\n";
    }

    echo "Admin authentication is active. Access dashboard at /admin/login.php\n\n";
} catch (\Throwable $e) {
    die("\n[DATABASE ERROR]: " . $e->getMessage() . "\n");
}
