<?php
/**
 * DARK MATTER — Elite Fitness
 * Database Restoration Utility
 *
 * Restores a SQL or GZ backup archive to MySQL database.
 * Usage:
 *   php scripts/restore.php [path_to_backup_file]
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access Denied: Restore utility must be executed from CLI.\n");
}

require_once __DIR__ . '/../includes/Env.php';
require_once __DIR__ . '/../includes/Database.php';

use DarkMatter\Database;

echo "\n=======================================================\n";
echo "  DARK MATTER — DATABASE RESTORATION UTILITY           \n";
echo "=======================================================\n\n";

$backupFile = $argv[1] ?? null;

if (!$backupFile) {
    // Find latest backup in backups/
    $backupDir = dirname(__DIR__) . '/backups';
    $files = glob($backupDir . '/*.sql*');
    if (empty($files)) {
        die("Error: No backup archives detected in {$backupDir}. Provide explicit path.\n");
    }
    rsort($files);
    $backupFile = $files[0];
    echo "[INFO] No file specified. Selected latest backup archive:\n";
    echo "       {$backupFile}\n\n";
}

if (!file_exists($backupFile)) {
    die("Error: Backup file not found at: {$backupFile}\n");
}

try {
    $pdo = Database::getConnection();
    echo "[1/3] Connected to target MySQL database.\n";

    echo "[2/3] Reading and decompressing archive...\n";
    if (str_ends_with($backupFile, '.gz')) {
        $sql = gzdecode((string) file_get_contents($backupFile));
    } else {
        $sql = file_get_contents($backupFile);
    }

    if (empty($sql)) {
        die("Error: Failed to read backup SQL payload.\n");
    }

    echo "[3/3] Restoring database schema and data...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n[SUCCESS] Database successfully restored from:\n";
    echo "          {$backupFile}\n\n";
} catch (\Throwable $e) {
    die("\n[RESTORE FAILED]: " . $e->getMessage() . "\n");
}
