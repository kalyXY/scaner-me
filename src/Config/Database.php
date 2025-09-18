<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use App\Exceptions\DatabaseException;

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }

        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $config = self::$config;
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? '3306',
            $config['database'] ?? 'school_mvp'
        );

        try {
            $pdo = new PDO($dsn, $config['username'] ?? 'root', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // Set timezone
            $pdo->exec("SET time_zone = '+00:00'");

            return $pdo;
        } catch (PDOException $e) {
            throw new DatabaseException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function resetConnection(): void
    {
        self::$instance = null;
    }
}