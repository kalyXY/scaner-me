<?php
declare(strict_types=1);

/**
 * LEGACY COMPATIBILITY FILE
 * 
 * This file is kept for backward compatibility with the old system.
 * New code should use the modern application structure in src/.
 */

// Load the new application bootstrap
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\Database;
use App\Config\Config;

// Initialize the new system
if (!Config::get('app.name')) {
    // Load default configuration if not already loaded
    Config::set('app.environment', $_ENV['APP_ENV'] ?? 'production');
    Config::set('database.host', $_ENV['DB_HOST'] ?? '127.0.0.1');
    Config::set('database.port', $_ENV['DB_PORT'] ?? '3306');
    Config::set('database.database', $_ENV['DB_NAME'] ?? 'school_mvp');
    Config::set('database.username', $_ENV['DB_USER'] ?? 'root');
    Config::set('database.password', $_ENV['DB_PASS'] ?? '');

    Database::configure([
        'host' => Config::get('database.host'),
        'port' => Config::get('database.port'),
        'database' => Config::get('database.database'),
        'username' => Config::get('database.username'),
        'password' => Config::get('database.password'),
    ]);
}

date_default_timezone_set('UTC');

/**
 * Legacy function - use env() helper instead
 * @deprecated Use env() helper function
 */
function env(string $key, ?string $default = null): string
{
    return \env($key, $default);
}

/**
 * Legacy function - use Database::getConnection() instead
 * @deprecated Use App\Config\Database::getConnection()
 */
function get_pdo(): PDO
{
    return Database::getConnection();
}

/**
 * Legacy function - use responseJson() helper instead
 * @deprecated Use responseJson() helper function
 */
function respond_json(array $data, int $statusCode = 200): void
{
    responseJson($data, $statusCode);
}

