<?php

declare(strict_types=1);

namespace App\Models;

class Attendance extends BaseModel
{
    protected string $table = 'attendance';
    protected array $fillable = [
        'student_id', 'session_id', 'status', 'scanned_at', 'notes'
    ];

    public function recordAttendance(int $studentId, int $sessionId, string $status): bool
    {
        $sql = "
            INSERT INTO attendance (student_id, session_id, status, scanned_at) 
            VALUES (?, ?, ?, UTC_TIMESTAMP())
            ON DUPLICATE KEY UPDATE 
                status = VALUES(status), 
                scanned_at = VALUES(scanned_at)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$studentId, $sessionId, $status]);
    }

    public function getAttendanceByDate(string $date): array
    {
        $sql = "
            SELECT s.uuid, s.first_name, s.last_name, c.name AS course_name, 
                   cs.session_date, cs.start_time, a.status, a.scanned_at
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            JOIN course_sessions cs ON cs.id = a.session_id
            JOIN courses c ON c.id = cs.course_id
            WHERE cs.session_date = ?
            ORDER BY cs.start_time, s.last_name, s.first_name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        
        return $stmt->fetchAll();
    }

    public function getStudentAttendanceStats(int $studentId, string $startDate = null, string $endDate = null): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
            FROM attendance a
            JOIN course_sessions cs ON cs.id = a.session_id
            WHERE a.student_id = ?
        ";

        $params = [$studentId];

        if ($startDate) {
            $sql .= " AND cs.session_date >= ?";
            $params[] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND cs.session_date <= ?";
            $params[] = $endDate;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result ?: [];
    }
}