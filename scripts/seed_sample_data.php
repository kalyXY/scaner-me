<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Config\Config;

// Load configuration
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Configure database
Config::set('database.host', $_ENV['DB_HOST'] ?? '127.0.0.1');
Config::set('database.port', $_ENV['DB_PORT'] ?? '3306');
Config::set('database.database', $_ENV['DB_NAME'] ?? 'school_mvp');
Config::set('database.username', $_ENV['DB_USER'] ?? 'root');
Config::set('database.password', $_ENV['DB_PASS'] ?? '');

Database::configure([
    'host' => Config::get('database.host'),
    'port' => Config::get('database.port'),
    'database' => Config::get('database.database'),
    'username' => Config::get('database.username'),
    'password' => Config::get('database.password'),
]);

try {
    $pdo = Database::getConnection();
    
    echo "🚀 Seeding sample data...\n\n";

    // Clear existing data (optional)
    $clearData = $argv[1] ?? 'no';
    if ($clearData === '--clear') {
        echo "🧹 Clearing existing data...\n";
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $pdo->exec('TRUNCATE TABLE attendance');
        $pdo->exec('TRUNCATE TABLE exam_authorizations');
        $pdo->exec('TRUNCATE TABLE payments');
        $pdo->exec('TRUNCATE TABLE course_sessions');
        $pdo->exec('TRUNCATE TABLE exams');
        $pdo->exec('TRUNCATE TABLE courses');
        $pdo->exec('TRUNCATE TABLE students');
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        echo "✅ Data cleared\n\n";
    }

    // Sample students
    echo "👥 Creating students...\n";
    $students = [
        ['00000000-0000-0000-0000-000000000001', 'Alice', 'Dupont', '3A', 'alice.dupont@school.edu', '+33123456789'],
        ['00000000-0000-0000-0000-000000000002', 'Bob', 'Martin', '3A', 'bob.martin@school.edu', '+33123456790'],
        ['00000000-0000-0000-0000-000000000003', 'Claire', 'Bernard', '3B', 'claire.bernard@school.edu', '+33123456791'],
        ['00000000-0000-0000-0000-000000000004', 'David', 'Moreau', '3B', 'david.moreau@school.edu', '+33123456792'],
        ['00000000-0000-0000-0000-000000000005', 'Emma', 'Leroy', '3A', 'emma.leroy@school.edu', '+33123456793'],
        ['00000000-0000-0000-0000-000000000006', 'François', 'Roux', '3B', 'francois.roux@school.edu', '+33123456794'],
        ['00000000-0000-0000-0000-000000000007', 'Gabrielle', 'Blanc', '3A', 'gabrielle.blanc@school.edu', '+33123456795'],
        ['00000000-0000-0000-0000-000000000008', 'Hugo', 'Garnier', '3B', 'hugo.garnier@school.edu', '+33123456796'],
    ];

    $stmt = $pdo->prepare('INSERT INTO students (uuid, first_name, last_name, class_name, email, phone, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
    foreach ($students as $student) {
        $stmt->execute($student);
    }
    echo "✅ " . count($students) . " students created\n\n";

    // Sample courses
    echo "📚 Creating courses...\n";
    $courses = [
        ['MATH301', 'Mathématiques Avancées', '3A', 1, '08:00:00', '10:00:00'], // Monday
        ['PHYS301', 'Physique Quantique', '3A', 2, '10:15:00', '12:15:00'],    // Tuesday
        ['INFO301', 'Algorithmique', '3A', 3, '14:00:00', '16:00:00'],         // Wednesday
        ['MATH302', 'Analyse Numérique', '3B', 1, '14:00:00', '16:00:00'],     // Monday
        ['PHYS302', 'Thermodynamique', '3B', 2, '08:00:00', '10:00:00'],       // Tuesday
        ['INFO302', 'Base de Données', '3B', 4, '10:15:00', '12:15:00'],       // Thursday
    ];

    $stmt = $pdo->prepare('INSERT INTO courses (code, name, class_name, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($courses as $course) {
        $stmt->execute($course);
    }
    echo "✅ " . count($courses) . " courses created\n\n";

    // Sample exams
    echo "📝 Creating exams...\n";
    $today = new DateTime();
    $nextWeek = clone $today;
    $nextWeek->add(new DateInterval('P7D'));
    
    $exams = [
        [1, 'Examen Final Mathématiques', $nextWeek->format('Y-m-d'), '09:00:00', '11:00:00'],
        [2, 'Contrôle Physique Quantique', $nextWeek->format('Y-m-d'), '14:00:00', '16:00:00'],
        [3, 'TP Algorithmique', $nextWeek->format('Y-m-d'), '08:00:00', '10:00:00'],
    ];

    $stmt = $pdo->prepare('INSERT INTO exams (course_id, name, exam_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)');
    foreach ($exams as $exam) {
        $stmt->execute($exam);
    }
    echo "✅ " . count($exams) . " exams created\n\n";

    // Sample course sessions for today and tomorrow
    echo "🗓️ Creating course sessions...\n";
    $sessions = [];
    
    // Today's sessions
    $todayStr = $today->format('Y-m-d');
    $sessions[] = [1, $todayStr, '08:00:00', '10:00:00', 0, null, 10]; // Math class
    $sessions[] = [2, $todayStr, '10:15:00', '12:15:00', 0, null, 10]; // Physics class
    $sessions[] = [3, $todayStr, '14:00:00', '16:00:00', 0, null, 15]; // Info class
    
    // Tomorrow's sessions (including exams)
    $tomorrow = clone $today;
    $tomorrow->add(new DateInterval('P1D'));
    $tomorrowStr = $tomorrow->format('Y-m-d');
    $sessions[] = [4, $tomorrowStr, '14:00:00', '16:00:00', 0, null, 10]; // Math 3B
    $sessions[] = [5, $tomorrowStr, '08:00:00', '10:00:00', 0, null, 10]; // Physics 3B
    
    // Next week exam sessions
    $nextWeekStr = $nextWeek->format('Y-m-d');
    $sessions[] = [1, $nextWeekStr, '09:00:00', '11:00:00', 1, 1, 5]; // Math exam
    $sessions[] = [2, $nextWeekStr, '14:00:00', '16:00:00', 1, 2, 5]; // Physics exam
    $sessions[] = [3, $nextWeekStr, '08:00:00', '10:00:00', 1, 3, 5]; // Info exam

    $stmt = $pdo->prepare('INSERT INTO course_sessions (course_id, session_date, start_time, end_time, is_exam, exam_id, late_after_minutes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($sessions as $session) {
        $stmt->execute($session);
    }
    echo "✅ " . count($sessions) . " course sessions created\n\n";

    // Sample payments for exams
    echo "💳 Creating payments...\n";
    $payments = [];
    
    // Get student IDs
    $studentIds = $pdo->query('SELECT id, uuid FROM students ORDER BY id')->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Some students have paid, others haven't
    $paidStudents = array_slice(array_keys($studentIds), 0, 4); // First 4 students paid
    
    foreach ($paidStudents as $studentId) {
        $payments[] = [$studentId, 1, 50.00, 'EUR', 'exam', 'paid', date('Y-m-d H:i:s'), 'PAY_' . uniqid()];
        $payments[] = [$studentId, 2, 45.00, 'EUR', 'exam', 'paid', date('Y-m-d H:i:s'), 'PAY_' . uniqid()];
    }

    $stmt = $pdo->prepare('INSERT INTO payments (student_id, exam_id, amount, currency, type, status, paid_at, reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($payments as $payment) {
        $stmt->execute($payment);
    }
    echo "✅ " . count($payments) . " payments created\n\n";

    // Sample exam authorizations
    echo "🎫 Creating exam authorizations...\n";
    $authorizations = [];
    
    foreach ($paidStudents as $studentId) {
        $authorizations[] = [$studentId, 1, 1, date('Y-m-d H:i:s'), 'system', 'Payment verified'];
        $authorizations[] = [$studentId, 2, 1, date('Y-m-d H:i:s'), 'system', 'Payment verified'];
    }
    
    // Add denied authorizations for unpaid students
    $unpaidStudents = array_slice(array_keys($studentIds), 4);
    foreach ($unpaidStudents as $studentId) {
        $authorizations[] = [$studentId, 1, 0, null, 'system', 'Payment required'];
        $authorizations[] = [$studentId, 2, 0, null, 'system', 'Payment required'];
    }

    $stmt = $pdo->prepare('INSERT INTO exam_authorizations (student_id, exam_id, allowed, allowed_at, checked_by, notes) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($authorizations as $auth) {
        $stmt->execute($auth);
    }
    echo "✅ " . count($authorizations) . " exam authorizations created\n\n";

    // Sample attendance records
    echo "📋 Creating sample attendance...\n";
    $attendanceRecords = [];
    
    // Get today's session IDs
    $todaySessions = $pdo->query("SELECT id FROM course_sessions WHERE session_date = CURDATE()")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($todaySessions)) {
        // Some students attended today's first session
        $attendingStudents = array_slice(array_keys($studentIds), 0, 6);
        
        foreach ($attendingStudents as $i => $studentId) {
            $status = ($i < 4) ? 'present' : 'late'; // First 4 on time, rest late
            $scanTime = date('Y-m-d H:i:s', strtotime('today 08:' . sprintf('%02d', 5 + $i * 3) . ':00'));
            $attendanceRecords[] = [$studentId, $todaySessions[0], $status, $scanTime];
        }
    }

    if (!empty($attendanceRecords)) {
        $stmt = $pdo->prepare('INSERT INTO attendance (student_id, session_id, status, scanned_at) VALUES (?, ?, ?, ?)');
        foreach ($attendanceRecords as $record) {
            $stmt->execute($record);
        }
        echo "✅ " . count($attendanceRecords) . " attendance records created\n\n";
    }

    echo "🎉 Sample data seeding completed successfully!\n\n";
    echo "📊 Summary:\n";
    echo "   - Students: " . count($students) . "\n";
    echo "   - Courses: " . count($courses) . "\n";
    echo "   - Exams: " . count($exams) . "\n";
    echo "   - Sessions: " . count($sessions) . "\n";
    echo "   - Payments: " . count($payments) . "\n";
    echo "   - Authorizations: " . count($authorizations) . "\n";
    echo "   - Attendance: " . count($attendanceRecords) . "\n\n";

    echo "🔗 Test UUIDs for QR scanning:\n";
    foreach (array_slice($students, 0, 4) as $student) {
        echo "   - {$student[1]} {$student[2]}: {$student[0]}\n";
    }
    
    echo "\n🌐 Access the application:\n";
    echo "   - Dashboard: http://localhost:8080/dashboard\n";
    echo "   - Scanner: http://localhost:8080/scanner\n";
    echo "   - API Health: http://localhost:8080/api/health\n\n";

} catch (Exception $e) {
    echo "❌ Error seeding data: " . $e->getMessage() . "\n";
    exit(1);
}