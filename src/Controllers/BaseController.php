<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\LoggingService;

abstract class BaseController
{
    protected LoggingService $logger;

    public function __construct(LoggingService $logger = null)
    {
        $this->logger = $logger ?? new LoggingService();
    }

    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    protected function errorResponse(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'ok' => false,
            'error' => $message,
            'timestamp' => gmdate('c')
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->jsonResponse($response, $statusCode);
    }

    protected function successResponse(array $data = [], string $message = null): void
    {
        $response = [
            'ok' => true,
            'timestamp' => gmdate('c')
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if (!empty($data)) {
            $response['data'] = $data;
        }

        $this->jsonResponse($response);
    }

    protected function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
                $errors[$field] = "The {$field} field is required.";
            }
        }

        return $errors;
    }

    protected function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function getRequestData(): array
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        switch ($method) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (str_contains($contentType, 'application/json')) {
                    $json = file_get_contents('php://input');
                    return json_decode($json, true) ?? [];
                }
                return $_POST;
            case 'GET':
                return $_GET;
            default:
                return [];
        }
    }
}