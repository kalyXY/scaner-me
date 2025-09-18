<?php
/**
 * Configuration native pour PHP 8.0.30 + XAMPP MySQL
 */

// Configuration de base
define('APP_NAME', 'QR Attendance System');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'development');

// Configuration XAMPP MySQL par défaut
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'school_mvp');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuration des chemins
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');

// Configuration de timezone
date_default_timezone_set('Europe/Paris');

// Configuration d'erreurs pour le développement
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 si HTTPS

// Fonctions utilitaires globales
function env(string $key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function redirect(string $url, int $statusCode = 302): void {
    header("Location: $url", true, $statusCode);
    exit();
}

function responseJson(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}