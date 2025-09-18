<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\AttendanceService;
use App\Services\QrGeneratorService;
use App\Models\Student;
use App\Models\CourseSession;
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

echo "🎓 DÉMONSTRATION - Système de Présence QR\n";
echo "==========================================\n\n";

try {
    $attendanceService = new AttendanceService();
    $studentModel = new Student();
    $sessionModel = new CourseSession();

    // 1. Afficher les étudiants
    echo "👥 ÉTUDIANTS ENREGISTRÉS:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    $students = $studentModel->getActiveStudents();
    foreach ($students as $student) {
        echo sprintf("• %s %s (%s) - UUID: %s\n", 
            $student['first_name'], 
            $student['last_name'], 
            $student['class_name'],
            $student['uuid']
        );
    }
    echo "\n";

    // 2. Afficher les sessions d'aujourd'hui
    echo "📅 SESSIONS D'AUJOURD'HUI:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    $today = date('Y-m-d');
    $sessions = $attendanceService->getSessionsForDate($today);
    
    if (empty($sessions)) {
        echo "❌ Aucune session programmée pour aujourd'hui.\n";
        echo "💡 Créons une session de démonstration...\n\n";
        
        // Créer une session pour maintenant
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO course_sessions (course_id, session_date, start_time, end_time, late_after_minutes) 
            VALUES (1, CURDATE(), TIME(NOW()), ADDTIME(TIME(NOW()), '02:00:00'), 10)
            ON DUPLICATE KEY UPDATE id = id
        ");
        $stmt->execute();
        
        $sessions = $attendanceService->getSessionsForDate($today);
    }
    
    foreach ($sessions as $session) {
        $type = $session['is_exam'] ? '📝 EXAMEN' : '📚 COURS';
        echo sprintf("• %s %s - %s (%s-%s)\n", 
            $type,
            $session['course_name'],
            $session['class_name'] ?? 'Toutes classes',
            $session['start_time'],
            $session['end_time'] ?? 'Non définie'
        );
    }
    echo "\n";

    // 3. Simuler des scans
    echo "🔍 SIMULATION DE SCANS QR:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $testUuids = [
        '00000000-0000-0000-0000-000000000001', // Alice - doit être présente
        '00000000-0000-0000-0000-000000000002', // Bob - doit être présent
        'invalid-uuid-123',                      // UUID invalide
        '00000000-0000-0000-0000-000000000003', // Claire - doit être présente
    ];

    foreach ($testUuids as $uuid) {
        echo "🔄 Test scan UUID: $uuid\n";
        
        try {
            $result = $attendanceService->recordScan($uuid);
            
            echo sprintf("✅ Succès: %s - Statut: %s\n", 
                $result['student']['name'],
                $result['attendance']['status']
            );
            
            if (isset($result['exam'])) {
                $examStatus = $result['exam']['allowed'] ? '✅ AUTORISÉ' : '❌ REFUSÉ';
                echo "   🎯 Examen: $examStatus\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erreur: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }

    // 4. Afficher les présences d'aujourd'hui
    echo "📊 PRÉSENCES D'AUJOURD'HUI:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    $attendance = $attendanceService->getAttendanceForDate($today);
    
    if (empty($attendance)) {
        echo "ℹ️ Aucune présence enregistrée pour aujourd'hui.\n";
    } else {
        foreach ($attendance as $record) {
            $status = match($record['status']) {
                'present' => '✅ Présent',
                'late' => '⏰ En retard',
                'absent' => '❌ Absent',
                default => '❓ ' . $record['status']
            };
            
            echo sprintf("• %s %s - %s (%s)\n",
                $record['first_name'],
                $record['last_name'],
                $status,
                $record['scanned_at'] ?? 'Non scanné'
            );
        }
    }
    echo "\n";

    // 5. Générer des QR codes de démonstration
    echo "🎯 GÉNÉRATION DE QR CODES:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    if (!is_dir('assets')) {
        mkdir('assets', 0755, true);
    }
    if (!is_dir('assets/qr')) {
        mkdir('assets/qr', 0755, true);
    }

    $qrService = new QrGeneratorService();
    $count = $qrService->generateAllStudentQrCodes();
    echo "✅ $count codes QR générés dans assets/qr/\n\n";

    // 6. URLs de test
    echo "🌐 URLS POUR TESTER:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    echo "• Dashboard: http://localhost:8080/dashboard\n";
    echo "• Scanner: http://localhost:8080/scanner\n";
    echo "• API Health: http://localhost:8080/api/health\n";
    echo "• API Scan: POST http://localhost:8080/api/scan\n";
    echo "\n";

    // 7. Commandes de test curl
    echo "🧪 COMMANDES DE TEST:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    echo "# Test API Health:\n";
    echo "curl http://localhost:8080/api/health\n\n";
    
    echo "# Test scan étudiant:\n";
    echo "curl -X POST http://localhost:8080/api/scan \\\n";
    echo "  -H 'Content-Type: application/json' \\\n";
    echo "  -d '{\"uuid\": \"00000000-0000-0000-0000-000000000001\"}'\n\n";
    
    echo "# Test sessions du jour:\n";
    echo "curl 'http://localhost:8080/api/sessions?date=$today'\n\n";

    echo "🎉 Démonstration terminée avec succès!\n";
    echo "📚 Consultez TESTING.md pour un guide complet.\n";

} catch (Exception $e) {
    echo "❌ Erreur lors de la démonstration: " . $e->getMessage() . "\n";
    echo "💡 Vérifiez que la base de données est configurée et que les données de test sont chargées.\n";
    echo "   Commande: php scripts/seed_sample_data.php --clear\n";
    exit(1);
}