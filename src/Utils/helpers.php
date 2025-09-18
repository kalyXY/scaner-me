<?php

declare(strict_types=1);

use App\Config\Config;
use App\Config\Database;
use App\Services\LoggingService;

if (!function_exists('env')) {
    /**
     * Get environment variable with optional default value
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }

        // Convert string booleans
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'null':
                case '(null)':
                    return null;
                case 'empty':
                case '(empty)':
                    return '';
            }
        }

        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('db')) {
    /**
     * Get database connection
     */
    function db(): PDO
    {
        return Database::getConnection();
    }
}

if (!function_exists('logger')) {
    /**
     * Get logger instance
     */
    function logger(string $name = 'app'): LoggingService
    {
        static $loggers = [];
        
        if (!isset($loggers[$name])) {
            $loggers[$name] = new LoggingService($name);
        }
        
        return $loggers[$name];
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize input string
     */
    function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generateUuid')) {
    /**
     * Generate a UUID v4
     */
    function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant bits
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime for display
     */
    function formatDateTime(?string $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        if (!$datetime) {
            return '';
        }
        
        try {
            return (new DateTime($datetime))->format($format);
        } catch (Exception $e) {
            return $datetime;
        }
    }
}

if (!function_exists('isValidUuid')) {
    /**
     * Validate UUID format
     */
    function isValidUuid(string $uuid): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}

if (!function_exists('responseJson')) {
    /**
     * Send JSON response and exit
     */
    function responseJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
}

if (!function_exists('abort')) {
    /**
     * Abort with HTTP status code and message
     */
    function abort(int $statusCode, string $message = ''): void
    {
        http_response_code($statusCode);
        
        if (empty($message)) {
            $messages = [
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                422 => 'Unprocessable Entity',
                500 => 'Internal Server Error'
            ];
            $message = $messages[$statusCode] ?? 'Error';
        }
        
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            responseJson(['ok' => false, 'error' => $message], $statusCode);
        } else {
            echo "<h1>{$statusCode} - {$message}</h1>";
        }
        
        exit;
    }
}