<?php

declare(strict_types=1);

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap the application
try {
    $app = new App\Application();
    $app->run();
} catch (Throwable $e) {
    // Last resort error handling
    error_log('Fatal application error: ' . $e->getMessage());
    
    http_response_code(500);
    if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => false,
            'error' => 'Internal server error',
            'timestamp' => gmdate('c')
        ]);
    } else {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>The system is temporarily unavailable. Please try again later.</p>';
    }
}