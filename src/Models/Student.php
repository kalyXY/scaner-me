<?php

declare(strict_types=1);

namespace App\Models;

class Student extends BaseModel
{
    protected string $table = 'students';
    protected array $fillable = [
        'uuid', 'first_name', 'last_name', 'class_name', 'email', 'phone', 'is_active'
    ];

    public function findByUuid(string $uuid): ?array
    {
        return $this->findBy('uuid', $uuid);
    }

    public function getActiveStudents(): array
    {
        return $this->all(['is_active' => 1]);
    }

    public function getByClass(string $className): array
    {
        return $this->all(['class_name' => $className, 'is_active' => 1]);
    }

    public function getAttendanceHistory(int $studentId, string $startDate = null, string $endDate = null): array
    {
        $sql = "
            SELECT a.*, cs.session_date, cs.start_time, cs.end_time, c.name as course_name
            FROM attendance a
            JOIN course_sessions cs ON cs.id = a.session_id
            JOIN courses c ON c.id = cs.course_id
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

        $sql .= " ORDER BY cs.session_date DESC, cs.start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    public function getPaymentHistory(int $studentId): array
    {
        $sql = "
            SELECT p.*, e.name as exam_name
            FROM payments p
            LEFT JOIN exams e ON e.id = p.exam_id
            WHERE p.student_id = ?
            ORDER BY p.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId]);
        
        return $stmt->fetchAll();
    }
}