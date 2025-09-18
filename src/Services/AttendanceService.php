<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Models\CourseSession;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\ExamAuthorization;
use App\Exceptions\ValidationException;
use DateTimeImmutable;
use DateTimeZone;

class AttendanceService
{
    private Student $studentModel;
    private CourseSession $sessionModel;
    private Attendance $attendanceModel;
    private LoggingService $logger;

    public function __construct(
        Student $studentModel = null,
        CourseSession $sessionModel = null,
        Attendance $attendanceModel = null,
        LoggingService $logger = null
    ) {
        $this->studentModel = $studentModel ?? new Student();
        $this->sessionModel = $sessionModel ?? new CourseSession();
        $this->attendanceModel = $attendanceModel ?? new Attendance();
        $this->logger = $logger ?? new LoggingService();
    }

    public function recordScan(string $uuid): array
    {
        // Validate UUID
        if (empty($uuid) || strlen($uuid) < 8) {
            throw new ValidationException(['uuid' => 'Invalid or missing UUID']);
        }

        // Find student
        $student = $this->studentModel->findByUuid($uuid);
        if (!$student || !$student['is_active']) {
            $this->logger->warning('Scan attempt with invalid UUID', ['uuid' => $uuid]);
            throw new ValidationException(['student' => 'Student not found or inactive']);
        }

        // Find current session
        $session = $this->sessionModel->getCurrentSession();
        if (!$session) {
            $this->logger->info('Scan attempt outside session hours', [
                'student_id' => $student['id'],
                'uuid' => $uuid
            ]);
            throw new ValidationException(['session' => 'No active session right now']);
        }

        // Calculate attendance status
        $status = $this->calculateAttendanceStatus($session);

        // Record attendance
        $success = $this->attendanceModel->recordAttendance(
            (int)$student['id'],
            (int)$session['id'],
            $status
        );

        if (!$success) {
            throw new \RuntimeException('Failed to record attendance');
        }

        $result = [
            'ok' => true,
            'student' => [
                'id' => (int)$student['id'],
                'uuid' => $student['uuid'],
                'name' => $student['first_name'] . ' ' . $student['last_name'],
            ],
            'session' => [
                'id' => (int)$session['id'],
                'course' => $session['course_name'],
                'date' => $session['session_date'],
                'start_time' => $session['start_time'],
                'is_exam' => (bool)$session['is_exam'],
            ],
            'attendance' => [
                'status' => $status,
                'scanned_at' => gmdate('c'),
            ],
        ];

        // Handle exam authorization if needed
        if ($session['is_exam'] && !empty($session['exam_id'])) {
            $examAuth = $this->handleExamAuthorization(
                (int)$student['id'],
                (int)$session['exam_id']
            );
            $result['exam'] = ['allowed' => $examAuth];
        }

        $this->logger->info('Attendance recorded successfully', [
            'student_id' => $student['id'],
            'session_id' => $session['id'],
            'status' => $status
        ]);

        return $result;
    }

    private function calculateAttendanceStatus(array $session): string
    {
        $lateAfter = $session['late_after_minutes'] ?? 10;
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $startDateTime = new DateTimeImmutable(
            $session['session_date'] . ' ' . $session['start_time'],
            new DateTimeZone('UTC')
        );

        $diffMinutes = (int)floor(($now->getTimestamp() - $startDateTime->getTimestamp()) / 60);

        return ($diffMinutes > $lateAfter) ? 'late' : 'present';
    }

    private function handleExamAuthorization(int $studentId, int $examId): bool
    {
        // Check if student has paid for this exam
        $paymentModel = new Payment();
        $payment = $paymentModel->findBy('student_id', $studentId);
        $hasPaid = $payment && $payment['exam_id'] == $examId && $payment['status'] === 'paid';

        // Record authorization
        $authModel = new ExamAuthorization();
        $allowed = $hasPaid ? 1 : 0;
        
        $authModel->create([
            'student_id' => $studentId,
            'exam_id' => $examId,
            'allowed' => $allowed,
            'allowed_at' => $allowed ? gmdate('Y-m-d H:i:s') : null
        ]);

        return (bool)$allowed;
    }

    public function getSessionsForDate(string $date): array
    {
        return $this->sessionModel->getSessionsByDate($date);
    }

    public function getAttendanceForSession(int $sessionId): array
    {
        return $this->sessionModel->getAttendanceForSession($sessionId);
    }

    public function getAttendanceForDate(string $date): array
    {
        return $this->attendanceModel->getAttendanceByDate($date);
    }
}