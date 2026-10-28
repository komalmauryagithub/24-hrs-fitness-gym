<?php
/**
 * DARK MATTER — Elite Fitness
 * Database Backup Utility
 *
 * Can be run via CLI, cron schedule, or automated cloud maintenance.
 * Generates structured SQL backup with table DDL and row data.
 *
 * Usage:
 *   php scripts/backup.php
 */

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Access Denied: Backup utility must be executed from CLI.\n");
}

require_once __DIR__ . '/../includes/Env.php';
require_once __DIR__ . '/../includes/Database.php';

use DarkMatter\Database;
use DarkMatter\Env;

echo "\n=======================================================\n";
echo "  DARK MATTER — DATABASE TELEMETRY BACKUP              \n";
echo "=======================================================\n\n";

$backupDir = dirname(__DIR__) . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0750, true);
}

$timestamp = date('Y-m-d_His');
$backupFile = $backupDir . DIRECTORY_SEPARATOR . "darkmatter_backup_{$timestamp}.sql";

try {
    $pdo = Database::getConnection();
    echo "[1/3] Connected to MySQL database.\n";

    $tables = [];
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    echo "[2/3] Exporting schema & data for " . count($tables) . " tables...\n";

    $out = "-- ====================================================\n";
    $out .= "-- DARK MATTER DATABASE BACKUP\n";
    $out .= "-- Generated: " . date('Y-m-d H:i:s') . " UTC\n";
    $out .= "-- Tables: " . implode(', ', $tables) . "\n";
    $out .= "-- ====================================================\n\n";
    $out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
    $out .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $out .= "SET NAMES utf8mb4;\n\n";

    foreach ($tables as $table) {
        echo "      -> Backing up table: `{$table}`\n";

        // Table DDL
        $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
        $out .= "-- Table structure for `{$table}`\n";
        $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $out .= $createStmt['Create Table'] . ";\n\n";

        // Table Data
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $out .= "-- Dumping data for `{$table}`\n";
            $columns = array_keys($rows[0]);
            $colList = '`' . implode('`, `', $columns) . '`';

            foreach ($rows as $r) {
                $values = [];
                foreach ($r as $val) {
                    if ($val === null) {
                        $values[] = 'NULL';
                    } else {
                        $values[] = $pdo->quote((string)$val);
                    }
                }
                $valList = implode(', ', $values);
                $out .= "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList});\n";
            }
            $out .= "\n";
        }
    }

    $out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    file_put_contents($backupFile, $out);
    $bytes = filesize($backupFile);

    // Compress with gzip if zlib enabled
    if (function_exists('gzencode')) {
        $gzFile = $backupFile . '.gz';
        file_put_contents($gzFile, gzencode($out, 9));
        @unlink($backupFile);
        $finalFile = $gzFile;
        $bytes = filesize($finalFile);
    } else {
        $finalFile = $backupFile;
    }

    echo "\n[3/3] Backup archive created successfully!\n";
    echo "      Path: {$finalFile}\n";
    echo "      Size: " . number_format($bytes / 1024, 2) . " KB\n\n";
} catch (\Throwable $e) {
    die("\n[BACKUP FAILED]: " . $e->getMessage() . "\n");
}
