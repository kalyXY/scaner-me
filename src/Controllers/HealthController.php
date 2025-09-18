<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Services\LoggingService;

class HealthController extends BaseController
{
    public function check(): void
    {
        try {
            $checks = [
                'database' => $this->checkDatabase(),
                'filesystem' => $this->checkFilesystem(),
                'php_extensions' => $this->checkPhpExtensions(),
            ];

            $allHealthy = array_reduce($checks, fn($carry, $check) => $carry && $check['status'] === 'ok', true);

            $response = [
                'ok' => $allHealthy,
                'status' => $allHealthy ? 'healthy' : 'unhealthy',
                'timestamp' => gmdate('c'),
                'checks' => $checks,
                'version' => '2.0.0'
            ];

            $statusCode = $allHealthy ? 200 : 503;
            $this->jsonResponse($response, $statusCode);

        } catch (\Throwable $e) {
            $this->logger->error('Health check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->jsonResponse([
                'ok' => false,
                'status' => 'error',
                'error' => 'Health check failed',
                'timestamp' => gmdate('c')
            ], 503);
        }
    }

    private function checkDatabase(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->query('SELECT 1');
            $result = $stmt->fetchColumn();

            return [
                'status' => $result === 1 ? 'ok' : 'error',
                'message' => $result === 1 ? 'Database connection successful' : 'Database query failed'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }
    }

    private function checkFilesystem(): array
    {
        $checks = [];
        $paths = [
            'logs' => __DIR__ . '/../../logs',
            'assets' => __DIR__ . '/../../assets',
            'qr_output' => __DIR__ . '/../../assets/qr'
        ];

        foreach ($paths as $name => $path) {
            $checks[$name] = [
                'path' => $path,
                'exists' => is_dir($path),
                'writable' => is_writable($path)
            ];
        }

        $allOk = array_reduce($checks, fn($carry, $check) => $carry && $check['exists'] && $check['writable'], true);

        return [
            'status' => $allOk ? 'ok' : 'error',
            'message' => $allOk ? 'All directories accessible' : 'Some directories have issues',
            'details' => $checks
        ];
    }

    private function checkPhpExtensions(): array
    {
        $required = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
        $missing = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'status' => empty($missing) ? 'ok' : 'error',
            'message' => empty($missing) ? 'All required extensions loaded' : 'Missing extensions: ' . implode(', ', $missing),
            'required' => $required,
            'missing' => $missing
        ];
    }
}