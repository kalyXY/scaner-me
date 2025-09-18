<?php

declare(strict_types=1);

namespace App;

use App\Config\Config;
use App\Config\Database;
use App\Services\LoggingService;
use App\Controllers\AttendanceController;
use App\Controllers\DashboardController;
use App\Controllers\ExportController;
use App\Controllers\HealthController;
use App\Exceptions\ValidationException;
use App\Exceptions\AuthenticationException;
use Dotenv\Dotenv;

class Application
{
    private LoggingService $logger;
    private array $routes = [];

    public function __construct()
    {
        $this->loadEnvironment();
        $this->loadConfiguration();
        $this->initializeDatabase();
        $this->logger = new LoggingService();
        $this->registerRoutes();
    }

    private function loadEnvironment(): void
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
    }

    private function loadConfiguration(): void
    {
        $configPath = __DIR__ . '/../config/app.php';
        if (file_exists($configPath)) {
            Config::load($configPath);
        } else {
            // Default configuration
            Config::set('app.environment', $_ENV['APP_ENV'] ?? 'production');
            Config::set('app.debug', $_ENV['APP_DEBUG'] ?? false);
            Config::set('database.host', $_ENV['DB_HOST'] ?? '127.0.0.1');
            Config::set('database.port', $_ENV['DB_PORT'] ?? '3306');
            Config::set('database.database', $_ENV['DB_NAME'] ?? 'school_mvp');
            Config::set('database.username', $_ENV['DB_USER'] ?? 'root');
            Config::set('database.password', $_ENV['DB_PASS'] ?? '');
            Config::set('logging.level', $_ENV['LOG_LEVEL'] ?? 'INFO');
            Config::set('logging.path', $_ENV['LOG_PATH'] ?? __DIR__ . '/../logs/app.log');
        }
    }

    private function initializeDatabase(): void
    {
        Database::configure([
            'host' => Config::get('database.host'),
            'port' => Config::get('database.port'),
            'database' => Config::get('database.database'),
            'username' => Config::get('database.username'),
            'password' => Config::get('database.password'),
        ]);
    }

    private function registerRoutes(): void
    {
        // API Routes
        $this->routes['GET']['/api/health'] = [HealthController::class, 'check'];
        $this->routes['GET']['/api/scan'] = [AttendanceController::class, 'scan'];
        $this->routes['POST']['/api/scan'] = [AttendanceController::class, 'scan'];
        $this->routes['GET']['/api/sessions'] = [AttendanceController::class, 'getSessionsForDate'];
        $this->routes['GET']['/api/attendance/session'] = [AttendanceController::class, 'getAttendanceForSession'];
        $this->routes['GET']['/api/attendance/date'] = [AttendanceController::class, 'getAttendanceForDate'];
        $this->routes['GET']['/api/dashboard'] = [DashboardController::class, 'apiData'];
        $this->routes['GET']['/api/export/csv'] = [ExportController::class, 'exportCsv'];

        // Web Routes
        $this->routes['GET']['/'] = [DashboardController::class, 'index'];
        $this->routes['GET']['/dashboard'] = [DashboardController::class, 'index'];
        $this->routes['GET']['/export/csv'] = [ExportController::class, 'exportCsv'];

        // Legacy compatibility
        $this->routes['GET']['/scan.php'] = [AttendanceController::class, 'scan'];
        $this->routes['POST']['/scan.php'] = [AttendanceController::class, 'scan'];
        $this->routes['GET']['/dashboard.php'] = [DashboardController::class, 'index'];
        $this->routes['GET']['/export_csv.php'] = [ExportController::class, 'exportCsv'];
    }

    public function run(): void
    {
        try {
            $this->handleCors();
            $this->route();
        } catch (ValidationException $e) {
            $this->handleValidationException($e);
        } catch (AuthenticationException $e) {
            $this->handleAuthenticationException($e);
        } catch (\Throwable $e) {
            $this->handleGenericException($e);
        }
    }

    private function handleCors(): void
    {
        // Handle CORS for API requests
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                exit(0);
            }
        }
    }

    private function route(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        
        // Remove trailing slash except for root
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        if (!isset($this->routes[$method][$uri])) {
            $this->notFound();
            return;
        }

        [$controllerClass, $method] = $this->routes[$method][$uri];
        
        $controller = new $controllerClass();
        $controller->$method();
    }

    private function notFound(): void
    {
        http_response_code(404);
        
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            header('Content-Type: application/json');
            echo json_encode([
                'ok' => false,
                'error' => 'Endpoint not found',
                'timestamp' => gmdate('c')
            ]);
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }

    private function handleValidationException(ValidationException $e): void
    {
        http_response_code(422);
        header('Content-Type: application/json');
        
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage(),
            'errors' => $e->getErrors(),
            'timestamp' => gmdate('c')
        ]);
    }

    private function handleAuthenticationException(AuthenticationException $e): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        
        echo json_encode([
            'ok' => false,
            'error' => $e->getMessage(),
            'timestamp' => gmdate('c')
        ]);
    }

    private function handleGenericException(\Throwable $e): void
    {
        $this->logger->error('Unhandled exception', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

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
            if (Config::get('app.debug', false)) {
                echo '<pre>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
            }
        }
    }
}