<?php
/**
 * DARK MATTER — Elite Fitness
 * PDO Database Connection & Query Abstraction
 * Secure, prepared-statements only, no raw error leakage to visitors.
 */

declare(strict_types=1);

namespace DarkMatter;

use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ . '/Env.php';

class Database
{
    private static ?PDO $instance = null;

    /**
     * Private constructor to prevent direct instantiation.
     */
    private function __construct() {}

    /**
     * Get or initialize the PDO connection instance.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = (string) Env::get('DB_HOST', '127.0.0.1');
        $port = (int) Env::get('DB_PORT', 3306);
        $dbname = (string) Env::get('DB_NAME', 'fitness_gym');
        $user = (string) Env::get('DB_USER', 'root');
        $pass = (string) Env::get('DB_PASS', '');
        $charset = (string) Env::get('DB_CHARSET', 'utf8mb4');

        // Support cloud connection strings (DATABASE_URL / MYSQL_URL)
        $dbUrl = Env::get('DATABASE_URL', Env::get('MYSQL_URL'));
        if (!empty($dbUrl) && is_string($dbUrl)) {
            $parsed = parse_url($dbUrl);
            if ($parsed && isset($parsed['host'])) {
                $host = $parsed['host'];
                $port = (int) ($parsed['port'] ?? 3306);
                $user = isset($parsed['user']) ? urldecode($parsed['user']) : $user;
                $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : $pass;
                if (!empty($parsed['path'])) {
                    $dbname = ltrim($parsed['path'], '/');
                }
            }
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        // Ensure clean charset configuration across PHP 8.0 through PHP 8.5+
        if (defined('\Pdo\Mysql::ATTR_INIT_COMMAND')) {
            $options[\Pdo\Mysql::ATTR_INIT_COMMAND] = "SET NAMES {$charset} COLLATE {$charset}_unicode_ci";
        } elseif (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            @$options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$charset} COLLATE {$charset}_unicode_ci";
        }

        // SSL / TLS configuration for cloud-managed MySQL databases
        $sslCa = (string) Env::get('DB_SSL_CA', '');
        $sslVerify = (bool) Env::get('DB_SSL_VERIFY_SERVER_CERT', true);

        $sslCaConst = defined('\Pdo\Mysql::ATTR_SSL_CA')
            ? \Pdo\Mysql::ATTR_SSL_CA
            : (defined('PDO::MYSQL_ATTR_SSL_CA') ? PDO::MYSQL_ATTR_SSL_CA : null);

        $sslVerifyConst = defined('\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
            ? \Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT
            : (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT') ? PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT : null);

        if ($sslCaConst !== null && $sslCa !== '') {
            if (file_exists($sslCa)) {
                $options[$sslCaConst] = $sslCa;
            }
            if ($sslVerifyConst !== null) {
                $options[$sslVerifyConst] = $sslVerify;
            }
        }

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            return self::$instance;
        } catch (PDOException $e) {
            // Log real database error securely to system log / file
            error_log('[DARK MATTER DATABASE ERROR]: ' . $e->getMessage());

            $debug = (bool) Env::get('APP_DEBUG', false);
            if ($debug) {
                throw new RuntimeException("Database Connection Error: " . $e->getMessage());
            }

            // Safe user-facing error message without credential leakage
            throw new RuntimeException("Secure connection to Dark Matter database telemetry is temporarily unavailable. Please retry shortly.");
        }
    }

    /**
     * Execute a prepared SELECT query and fetch all matching records.
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('[DARK MATTER QUERY ERROR]: ' . $e->getMessage() . " | SQL: {$sql}");
            throw new RuntimeException("Data retrieval failed. Please try again.");
        }
    }

    /**
     * Execute a prepared SELECT query and fetch a single record.
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result === false ? null : $result;
        } catch (PDOException $e) {
            error_log('[DARK MATTER QUERY ERROR]: ' . $e->getMessage() . " | SQL: {$sql}");
            throw new RuntimeException("Data retrieval failed. Please try again.");
        }
    }

    /**
     * Execute a prepared INSERT/UPDATE/DELETE query and return affected rows count.
     */
    public static function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log('[DARK MATTER EXEC ERROR]: ' . $e->getMessage() . " | SQL: {$sql}");
            throw new RuntimeException("Data execution failed. Please verify submission.");
        }
    }

    /**
     * Get ID of the last inserted record.
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Transaction control wrappers.
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }

    /**
     * Test connection liveness without exposing telemetry or credentials.
     */
    public static function ping(): bool
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query('SELECT 1');
            return (bool) $stmt;
        } catch (\Throwable $e) {
            error_log('[DATABASE PING ERROR]: ' . $e->getMessage());
            return false;
        }
    }
}

