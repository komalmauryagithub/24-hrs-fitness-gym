<?php
/**
 * DARK MATTER — Elite Fitness
 * Automated Database Migration & Schema Provisioning Utility
 *
 * Runs on local setup, CI/CD pipelines, and cloud container release phases.
 * Usage:
 *   php scripts/migrate.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/Env.php';
require_once __DIR__ . '/../includes/Database.php';

use DarkMatter\Database;
use DarkMatter\Env;

echo "\n=======================================================\n";
echo "  DARK MATTER — DATABASE MIGRATION & PROVISIONING      \n";
echo "=======================================================\n\n";

try {
    $pdo = Database::getConnection();
    echo "[1/4] Database connection established successfully.\n";

    // 1. Run Schema Definition
    $schemaFile = dirname(__DIR__) . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Error: Schema file not found at {$schemaFile}\n");
    }

    echo "[2/4] Executing schema DDL migrations...\n";
    $schemaSql = file_get_contents($schemaFile);

    // Split and execute SQL statements safely
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec($schemaSql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "      -> Tables verified and schema applied successfully.\n";

    // 2. Check and Apply Demo Seed Data if empty
    echo "[3/4] Checking existing catalog data...\n";
    $planCount = (int) ($pdo->query("SELECT COUNT(*) FROM `membership_plans`")->fetchColumn() ?? 0);

    if ($planCount === 0) {
        $seedFile = dirname(__DIR__) . '/database/seed.sql';
        if (file_exists($seedFile)) {
            echo "      -> Database empty. Importing core seed data...\n";
            $seedSql = file_get_contents($seedFile);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec($seedSql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            echo "      -> Seed catalog (plans, coaches, protocols, gallery) applied.\n";
        }
    } else {
        echo "      -> Existing catalog data found ({$planCount} plans). Skipping seed data.\n";
    }

    // 3. Initial Admin Provisioning (Zero-Touch Cloud Deployment)
    echo "[4/4] Verifying administrator accounts...\n";
    $adminCount = (int) ($pdo->query("SELECT COUNT(*) FROM `admins`")->fetchColumn() ?? 0);

    if ($adminCount === 0) {
        $initUser = (string) Env::get('ADMIN_INIT_USERNAME', '');
        $initMail = (string) Env::get('ADMIN_INIT_EMAIL', '');
        $initPass = (string) Env::get('ADMIN_INIT_PASSWORD', '');

        if ($initUser !== '' && $initMail !== '' && $initPass !== '') {
            $hash = password_hash($initPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare(
                "INSERT INTO `admins` (`username`, `email`, `password_hash`, `role`, `created_at`)
                 VALUES (:u, :e, :h, 'superadmin', NOW())"
            );
            $stmt->execute([
                ':u' => $initUser,
                ':e' => $initMail,
                ':h' => $hash
            ]);
            echo "      -> Primary administrator '{$initUser}' provisioned automatically via environment variables.\n";
        } else {
            echo "      -> [NOTICE] No admin account detected. Provision one with:\n";
            echo "         php scripts/create_admin.php <username> <email> <password>\n";
            echo "         Or configure ADMIN_INIT_USERNAME, ADMIN_INIT_EMAIL, ADMIN_INIT_PASSWORD in .env\n";
        }
    } else {
        echo "      -> {$adminCount} administrator account(s) active.\n";
    }

    echo "\n[SUCCESS] Migration and telemetry initialization complete.\n\n";
} catch (\Throwable $e) {
    die("\n[MIGRATION FAILED]: " . $e->getMessage() . "\n");
}
