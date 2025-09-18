<?php

declare(strict_types=1);

namespace App\Models;

class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected array $fillable = [
        'student_id', 'exam_id', 'amount', 'currency', 'type', 
        'status', 'paid_at', 'reference'
    ];

    public function getRecentPayments(int $limit = 20): array
    {
        $sql = "
            SELECT p.id, s.first_name, s.last_name, p.type, p.amount, 
                   p.currency, p.status, p.paid_at
            FROM payments p
            JOIN students s ON s.id = p.student_id
            ORDER BY p.created_at DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }

    public function findByStudentAndExam(int $studentId, int $examId): ?array
    {
        $sql = "SELECT * FROM payments WHERE student_id = ? AND exam_id = ? AND status = 'paid' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$studentId, $examId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }
}