<?php

return [
    'app' => [
        'name' => env('APP_NAME', 'QR Attendance System'),
        'environment' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost'),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],

    'database' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_NAME', 'school_mvp'),
        'username' => env('DB_USER', 'root'),
        'password' => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ],
    ],

    'logging' => [
        'level' => env('LOG_LEVEL', 'INFO'),
        'path' => env('LOG_PATH', __DIR__ . '/../logs/app.log'),
        'max_files' => env('LOG_MAX_FILES', 30),
    ],

    'security' => [
        'csrf_protection' => env('CSRF_PROTECTION', true),
        'jwt_secret' => env('JWT_SECRET', 'your-secret-key-change-this'),
        'jwt_expiration' => env('JWT_EXPIRATION', 3600), // 1 hour
        'rate_limit' => [
            'enabled' => env('RATE_LIMIT_ENABLED', true),
            'requests_per_minute' => env('RATE_LIMIT_RPM', 60),
        ],
    ],

    'cache' => [
        'enabled' => env('CACHE_ENABLED', true),
        'ttl' => env('CACHE_TTL', 300), // 5 minutes
    ],

    'qr' => [
        'size' => env('QR_SIZE', 300),
        'margin' => env('QR_MARGIN', 10),
        'output_dir' => env('QR_OUTPUT_DIR', __DIR__ . '/../assets/qr'),
    ],

    'attendance' => [
        'default_late_minutes' => env('DEFAULT_LATE_MINUTES', 10),
        'max_scan_window_hours' => env('MAX_SCAN_WINDOW_HOURS', 2),
    ],
];