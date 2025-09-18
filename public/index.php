<?php

declare(strict_types=1);

// Bootstrap de l'application PHP native
require_once __DIR__ . '/../bootstrap.php';

// Routage simple
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Routes API
if (str_starts_with($uri, '/api/')) {
    handleApiRequest($uri, $method);
} else {
    // Routes web
    handleWebRequest($uri);
}

function handleApiRequest(string $uri, string $method): void {
    // Supprimer le préfixe /api
    $path = substr($uri, 4);
    
    // Headers CORS pour les API
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    try {
        switch ($path) {
            case '/health':
                $controller = new App\Controllers\HealthController();
                $controller->check();
                break;
                
            case '/students':
                $controller = new App\Controllers\AttendanceController();
                if ($method === 'GET') {
                    $controller->getStudents();
                } elseif ($method === 'POST') {
                    $controller->addStudent();
                }
                break;
                
            case '/attendance':
                $controller = new App\Controllers\AttendanceController();
                if ($method === 'POST') {
                    $controller->markAttendance();
                } elseif ($method === 'GET') {
                    $controller->getAttendance();
                }
                break;
                
            case '/export/csv':
                $controller = new App\Controllers\ExportController();
                $controller->exportCsv();
                break;
                
            default:
                responseJson(['ok' => false, 'error' => 'Route non trouvée'], 404);
        }
    } catch (Exception $e) {
        responseJson(['ok' => false, 'error' => $e->getMessage()], 500);
    }
}

function handleWebRequest(string $uri): void {
    switch ($uri) {
        case '/':
        case '/dashboard':
            include __DIR__ . '/dashboard.php';
            break;
            
        case '/scan':
        case '/scanner':
            include __DIR__ . '/scanner.php';
            break;
            
        default:
            http_response_code(404);
            echo '<h1>404 - Page non trouvée</h1>';
    }
}