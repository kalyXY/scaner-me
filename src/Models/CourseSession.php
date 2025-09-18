<?php

declare(strict_types=1);

namespace App\Models;

class CourseSession extends BaseModel
{
    protected string $table = 'course_sessions';
    protected array $fillable = [
        'course_id', 'session_date', 'start_time', 'end_time', 
        'is_exam', 'exam_id', 'late_after_minutes'
    ];

    public function getCurrentSession(): ?array
    {
        $sql = "
            SELECT cs.*, c.name AS course_name, c.class_name AS course_class
            FROM course_sessions cs
            JOIN courses c ON c.id = cs.course_id
            WHERE cs.session_date = CURDATE()
              AND TIME(NOW()) BETWEEN cs.start_time AND COALESCE(cs.end_time, ADDTIME(cs.start_time, '02:00:00'))
            ORDER BY cs.start_time ASC 
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    public function getSessionsByDate(string $date): array
    {
        $sql = "
            SELECT cs.id, c.name AS course_name, c.class_name, 
                   cs.session_date, cs.start_time, cs.end_time, cs.is_exam,
                   (SELECT COUNT(*) FROM students s WHERE s.class_name = c.class_name AND s.is_active = 1) AS enrolled_count,
                   SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                   SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                   COUNT(a.id) AS scanned_count
            FROM course_sessions cs
            JOIN courses c ON c.id = cs.course_id
            LEFT JOIN attendance a ON a.session_id = cs.id
            WHERE cs.session_date = ?
            GROUP BY cs.id
            ORDER BY cs.start_time ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$date]);
        
        return $stmt->fetchAll();
    }

    public function getAttendanceForSession(int $sessionId): array
    {
        $sql = "
            SELECT s.uuid, s.first_name, s.last_name, c.name AS course_name, 
                   cs.session_date, cs.start_time, a.status, a.scanned_at
            FROM attendance a
            JOIN students s ON s.id = a.student_id
            JOIN course_sessions cs ON cs.id = a.session_id
            JOIN courses c ON c.id = cs.course_id
            WHERE a.session_id = ?
            ORDER BY s.last_name, s.first_name
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
        
        return $stmt->fetchAll();
    }
}