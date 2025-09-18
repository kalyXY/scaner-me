<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AttendanceService;
use App\Services\LoggingService;
use League\Csv\Writer;
use League\Csv\Exception;

class ExportController extends BaseController
{
    private AttendanceService $attendanceService;

    public function __construct(
        AttendanceService $attendanceService = null,
        LoggingService $logger = null
    ) {
        parent::__construct($logger);
        $this->attendanceService = $attendanceService ?? new AttendanceService();
    }

    public function exportCsv(): void
    {
        try {
            $data = $this->getRequestData();
            $date = $data['date'] ?? null;
            $sessionId = isset($data['session_id']) ? (int)$data['session_id'] : null;

            if (!$date && !$sessionId) {
                $date = date('Y-m-d');
            }

            // Validate date format if provided
            if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $this->errorResponse('Invalid date format. Use YYYY-MM-DD', 400);
                return;
            }

            // Get attendance data
            if ($sessionId) {
                $attendance = $this->attendanceService->getAttendanceForSession($sessionId);
                $filename = "attendance_session_{$sessionId}.csv";
            } else {
                $attendance = $this->attendanceService->getAttendanceForDate($date);
                $filename = "attendance_{$date}.csv";
            }

            // Create CSV
            $csv = Writer::createFromString('');
            
            // Add BOM for Excel compatibility
            $csv->setOutputBOM(Writer::BOM_UTF8);

            // Add headers
            $csv->insertOne([
                'Student UUID',
                'First Name', 
                'Last Name',
                'Course',
                'Date',
                'Start Time',
                'Status',
                'Scanned At'
            ]);

            // Add data rows
            foreach ($attendance as $row) {
                $csv->insertOne([
                    $row['uuid'] ?? '',
                    $row['first_name'] ?? '',
                    $row['last_name'] ?? '',
                    $row['course_name'] ?? '',
                    $row['session_date'] ?? '',
                    $row['start_time'] ?? '',
                    $row['status'] ?? '',
                    $row['scanned_at'] ?? ''
                ]);
            }

            // Set headers for file download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

            // Output CSV
            echo $csv->toString();

            $this->logger->info('CSV export completed', [
                'date' => $date,
                'session_id' => $sessionId,
                'records_count' => count($attendance)
            ]);

        } catch (Exception $e) {
            $this->logger->error('CSV export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Failed to generate CSV export', 500);
        } catch (\Throwable $e) {
            $this->logger->error('Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorResponse('Internal server error', 500);
        }
    }
}