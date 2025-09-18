<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AttendanceService;
use App\Exceptions\ValidationException;
use App\Services\LoggingService;

class AttendanceController extends BaseController
{
    private AttendanceService $attendanceService;

    public function __construct(
        AttendanceService $attendanceService = null,
        LoggingService $logger = null
    ) {
        parent::__construct($logger);
        $this->attendanceService = $attendanceService ?? new AttendanceService();
    }

    public function scan(): void
    {
        try {
            $data = $this->getRequestData();
            $uuid = $data['uuid'] ?? '';

            if (empty($uuid)) {
                $this->errorResponse('UUID is required', 400);
                return;
            }

            $result = $this->attendanceService->recordScan($uuid);
            $this->successResponse($result);

        } catch (ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->logger->error('Scan error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }

    public function getSessionsForDate(): void
    {
        try {
            $data = $this->getRequestData();
            $date = $data['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $this->errorResponse('Invalid date format. Use YYYY-MM-DD', 400);
                return;
            }

            $sessions = $this->attendanceService->getSessionsForDate($date);
            $this->successResponse(['sessions' => $sessions]);

        } catch (\Throwable $e) {
            $this->logger->error('Get sessions error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }

    public function getAttendanceForSession(): void
    {
        try {
            $data = $this->getRequestData();
            $sessionId = (int)($data['session_id'] ?? 0);

            if ($sessionId <= 0) {
                $this->errorResponse('Valid session_id is required', 400);
                return;
            }

            $attendance = $this->attendanceService->getAttendanceForSession($sessionId);
            $this->successResponse(['attendance' => $attendance]);

        } catch (\Throwable $e) {
            $this->logger->error('Get session attendance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }

    public function getAttendanceForDate(): void
    {
        try {
            $data = $this->getRequestData();
            $date = $data['date'] ?? date('Y-m-d');

            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $this->errorResponse('Invalid date format. Use YYYY-MM-DD', 400);
                return;
            }

            $attendance = $this->attendanceService->getAttendanceForDate($date);
            $this->successResponse(['attendance' => $attendance]);

        } catch (\Throwable $e) {
            $this->logger->error('Get date attendance error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }
}