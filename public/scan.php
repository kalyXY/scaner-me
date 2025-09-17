<?php
declare(strict_types=1);

require __DIR__ . '/../config.php';

try {
    $uuid = trim((string)($_GET['uuid'] ?? $_POST['uuid'] ?? ''));
    if ($uuid === '' || strlen($uuid) < 8) {
        respond_json(['ok' => false, 'error' => 'Missing or invalid uuid'], 400);
    }

    $pdo = get_pdo();

    // Find student by UUID
    $stmt = $pdo->prepare('SELECT id, uuid, first_name, last_name FROM students WHERE uuid = :uuid AND is_active = 1');
    $stmt->execute([':uuid' => $uuid]);
    $student = $stmt->fetch();
    if (!$student) {
        respond_json(['ok' => false, 'error' => 'Student not found or inactive'], 404);
    }

    // Find current session (today, current time inside window)
    $sessionSql = 'SELECT cs.*, c.name AS course_name, c.class_name AS course_class
                   FROM course_sessions cs
                   JOIN courses c ON c.id = cs.course_id
                   WHERE cs.session_date = CURDATE()
                     AND TIME(NOW()) BETWEEN cs.start_time AND COALESCE(cs.end_time, ADDTIME(cs.start_time, "02:00:00"))
                   ORDER BY cs.start_time ASC LIMIT 1';
    $session = $pdo->query($sessionSql)->fetch();
    if (!$session) {
        respond_json(['ok' => false, 'error' => 'No active session right now'], 404);
    }

    // Compute status based on lateness
    $lateAfter = is_null($session['late_after_minutes']) ? 10 : (int)$session['late_after_minutes'];
    $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    $startDateTime = new DateTimeImmutable($session['session_date'] . ' ' . $session['start_time'], new DateTimeZone('UTC'));
    $diffMinutes = (int)floor(($now->getTimestamp() - $startDateTime->getTimestamp()) / 60);
    $status = ($diffMinutes > $lateAfter) ? 'late' : 'present';

    // Insert or update attendance
    $ins = $pdo->prepare('INSERT INTO attendance (student_id, session_id, status, scanned_at) VALUES (:sid, :sess, :status, UTC_TIMESTAMP())
                          ON DUPLICATE KEY UPDATE status = VALUES(status), scanned_at = VALUES(scanned_at)');
    $ins->execute([
        ':sid' => (int)$student['id'],
        ':sess' => (int)$session['id'],
        ':status' => $status,
    ]);

    $isExam = (int)$session['is_exam'] === 1;
    $allowed = null;
    if ($isExam && !empty($session['exam_id'])) {
        // Verify payment for this exam
        $pay = $pdo->prepare('SELECT 1 FROM payments WHERE student_id = :sid AND exam_id = :eid AND status = "paid" LIMIT 1');
        $pay->execute([':sid' => (int)$student['id'], ':eid' => (int)$session['exam_id']]);
        $hasPaid = (bool)$pay->fetchColumn();

        $allowed = $hasPaid ? 1 : 0;
        $auth = $pdo->prepare('INSERT INTO exam_authorizations (student_id, exam_id, allowed, allowed_at)
                               VALUES (:sid, :eid, :allowed, IF(:allowed = 1, UTC_TIMESTAMP(), NULL))
                               ON DUPLICATE KEY UPDATE allowed = VALUES(allowed), allowed_at = VALUES(allowed_at)');
        $auth->execute([
            ':sid' => (int)$student['id'],
            ':eid' => (int)$session['exam_id'],
            ':allowed' => $allowed,
        ]);
    }

    respond_json([
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
            'is_exam' => $isExam,
        ],
        'attendance' => [
            'status' => $status,
            'scanned_at' => gmdate('c'),
        ],
        'exam' => $isExam ? ['allowed' => $allowed] : null,
    ]);
} catch (Throwable $e) {
    respond_json(['ok' => false, 'error' => 'Server error', 'detail' => $e->getMessage()], 500);
}

