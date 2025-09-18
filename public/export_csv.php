<?php
declare(strict_types=1);

// Legacy compatibility - redirect to new system
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ExportController;

$controller = new ExportController();
$controller->exportCsv();
exit;

$pdo = get_pdo();

$date = $_GET['date'] ?? null;
$sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : null;

if (!$date && !$sessionId) {
    $date = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="attendance_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student UUID', 'First Name', 'Last Name', 'Course', 'Date', 'Start Time', 'Status', 'Scanned At']);

if ($sessionId) {
    $stmt = $pdo->prepare('SELECT s.uuid, s.first_name, s.last_name, c.name AS course_name, cs.session_date, cs.start_time, a.status, a.scanned_at
                           FROM attendance a
                           JOIN students s ON s.id = a.student_id
                           JOIN course_sessions cs ON cs.id = a.session_id
                           JOIN courses c ON c.id = cs.course_id
                           WHERE a.session_id = :sid
                           ORDER BY s.last_name, s.first_name');
    $stmt->execute([':sid' => $sessionId]);
} else {
    $stmt = $pdo->prepare('SELECT s.uuid, s.first_name, s.last_name, c.name AS course_name, cs.session_date, cs.start_time, a.status, a.scanned_at
                           FROM attendance a
                           JOIN students s ON s.id = a.student_id
                           JOIN course_sessions cs ON cs.id = a.session_id
                           JOIN courses c ON c.id = cs.course_id
                           WHERE cs.session_date = :d
                           ORDER BY cs.start_time, s.last_name, s.first_name');
    $stmt->execute([':d' => $date]);
}

while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['uuid'],
        $row['first_name'],
        $row['last_name'],
        $row['course_name'],
        $row['session_date'],
        $row['start_time'],
        $row['status'],
        $row['scanned_at'],
    ]);
}

fclose($out);

