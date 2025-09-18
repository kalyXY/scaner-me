<?php

declare(strict_types=1);

namespace App\Models;

class ExamAuthorization extends BaseModel
{
    protected string $table = 'exam_authorizations';
    protected array $fillable = [
        'student_id', 'exam_id', 'allowed', 'allowed_at', 'checked_by', 'notes'
    ];

    public function getRecentAuthorizations(int $limit = 20): array
    {
        $sql = "
            SELECT ea.id, s.first_name, s.last_name, e.name AS exam_name, 
                   ea.allowed, ea.allowed_at
            FROM exam_authorizations ea
            JOIN students s ON s.id = ea.student_id
            JOIN exams e ON e.id = ea.exam_id
            ORDER BY ea.id DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }

    public function recordAuthorization(int $studentId, int $examId, bool $allowed): bool
    {
        $sql = "
            INSERT INTO exam_authorizations (student_id, exam_id, allowed, allowed_at)
            VALUES (?, ?, ?, IF(? = 1, UTC_TIMESTAMP(), NULL))
            ON DUPLICATE KEY UPDATE 
                allowed = VALUES(allowed), 
                allowed_at = VALUES(allowed_at)
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$studentId, $examId, $allowed ? 1 : 0, $allowed ? 1 : 0]);
    }
}