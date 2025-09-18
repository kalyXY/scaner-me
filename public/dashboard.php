<?php
declare(strict_types=1);

// Vérifier si on est appelé directement ou depuis index.php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../bootstrap.php';
}

$pdo = db();

$date = $_GET['date'] ?? null;
if (!$date) {
    $date = (new DateTimeImmutable('now', new DateTimeZone('Europe/Paris')))->format('Y-m-d');
}

// Statistiques générales
$stats = [];

// Total étudiants actifs
$stats['total_students'] = $pdo->query('SELECT COUNT(*) FROM students WHERE is_active = 1')->fetchColumn();

// Sessions du jour
$stats['today_sessions'] = $pdo->prepare('SELECT COUNT(*) FROM course_sessions WHERE session_date = ?');
$stats['today_sessions']->execute([$date]);
$stats['today_sessions'] = $stats['today_sessions']->fetchColumn();

// Présences du jour
$stats['today_attendance'] = $pdo->prepare('SELECT COUNT(*) FROM attendance a JOIN course_sessions cs ON cs.id = a.session_id WHERE cs.session_date = ?');
$stats['today_attendance']->execute([$date]);
$stats['today_attendance'] = $stats['today_attendance']->fetchColumn();

// Taux de présence
$stats['attendance_rate'] = $stats['today_sessions'] > 0 ? round(($stats['today_attendance'] / ($stats['today_sessions'] * $stats['total_students'])) * 100, 1) : 0;

// Sessions of the selected date with counts
$stmt = $pdo->prepare(
    'SELECT cs.id, c.name AS course_name, c.class_name, cs.session_date, cs.start_time, cs.end_time, cs.is_exam,
            (SELECT COUNT(*) FROM students s WHERE s.class_name = c.class_name AND s.is_active = 1) AS enrolled_count,
            SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) AS present_count,
            SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) AS late_count,
            COUNT(a.id) AS scanned_count
     FROM course_sessions cs
     JOIN courses c ON c.id = cs.course_id
     LEFT JOIN attendance a ON a.session_id = cs.id
     WHERE cs.session_date = :d
     GROUP BY cs.id
     ORDER BY cs.start_time ASC'
);
$stmt->execute([':d' => $date]);
$sessions = $stmt->fetchAll();

// Recent payments
$payments = $pdo->query(
    'SELECT p.id, s.first_name, s.last_name, p.type, p.amount, p.currency, p.status, p.paid_at
     FROM payments p
     JOIN students s ON s.id = p.student_id
     ORDER BY p.created_at DESC
     LIMIT 10'
)->fetchAll();

// Recent exam authorizations
$auths = $pdo->query(
    'SELECT ea.id, s.first_name, s.last_name, e.name AS exam_name, ea.allowed, ea.allowed_at
     FROM exam_authorizations ea
     JOIN students s ON s.id = ea.student_id
     JOIN exams e ON e.id = ea.exam_id
     ORDER BY ea.id DESC
     LIMIT 10'
)->fetchAll();

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/design-system.css">
    <link rel="stylesheet" href="assets/css/components.css">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="navbar-brand flex items-center gap-3">
                <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                        <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <span><?php echo APP_NAME; ?></span>
            </a>
            
            <div class="navbar-nav">
                <a href="/" class="navbar-link active">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="scanner" class="navbar-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9.5 6.5v3h-3v-3h3M11 5H5v6h6V5zm-1.5 9.5v3h-3v-3h3M11 13H5v6h6v-6zm6.5-6.5v3h-3v-3h3M19 5h-6v6h6V5zm-6.5 9.5v3h-3v-3h3M13 13h6v6h-6v-6zM21 21H3V3h18v18z"/>
                    </svg>
                    Scanner
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 animate-on-load">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Tableau de bord</h1>
                <p class="text-gray-600">Vue d'ensemble des présences et activités</p>
            </div>
            
            <div class="flex items-center gap-4 mt-4 md:mt-0">
                <form method="get" class="flex items-center gap-3 bg-white p-2 rounded-xl shadow-sm border">
                    <label for="date" class="text-sm font-medium text-gray-700">Date:</label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="<?php echo htmlspecialchars($date); ?>"
                           class="form-input border-0 p-2 focus:ring-0 text-sm">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                        Afficher
                    </button>
                </form>
                
                <a href="export_csv.php?date=<?php echo urlencode($date); ?>&scope=today" 
                   class="btn btn-outline btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid animate-on-load">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-value" data-stat="total_students"><?php echo $stats['total_students']; ?></div>
                <div class="stat-label">Étudiants actifs</div>
                <div class="stat-change">Total inscrit</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-value" data-stat="today_sessions"><?php echo $stats['today_sessions']; ?></div>
                <div class="stat-label">Sessions du jour</div>
                <div class="stat-change"><?php echo date('d/m/Y', strtotime($date)); ?></div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-value" data-stat="today_attendance"><?php echo $stats['today_attendance']; ?></div>
                <div class="stat-label">Présences du jour</div>
                <div class="stat-change">Scans effectués</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-value" data-stat="attendance_rate"><?php echo $stats['attendance_rate']; ?>%</div>
                <div class="stat-label">Taux de présence</div>
                <div class="stat-change">Moyenne du jour</div>
            </div>
        </div>

        <!-- Sessions Table -->
        <div class="card mb-8 animate-on-load">
            <div class="card-header">
                <h2 class="card-title">Sessions du <?php echo date('d/m/Y', strtotime($date)); ?></h2>
                <p class="card-subtitle">Détail des cours et examens programmés</p>
            </div>
            
            <div class="card-body p-0">
                <?php if (count($sessions) > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Classe</th>
                                <th>Cours</th>
                                <th>Type</th>
                                <th>Inscrits</th>
                                <th>Présents</th>
                                <th>Retards</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td class="font-mono text-sm">
                                    <?php echo htmlspecialchars($s['start_time']); ?>
                                    <?php if ($s['end_time']): ?>
                                        <span class="text-gray-400">- <?php echo htmlspecialchars($s['end_time']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($s['class_name'] ?? 'Toutes'); ?>
                                    </span>
                                </td>
                                <td class="font-medium"><?php echo htmlspecialchars($s['course_name']); ?></td>
                                <td>
                                    <?php if ((int)$s['is_exam'] === 1): ?>
                                        <span class="badge badge-warning">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            Examen
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Cours</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="font-semibold text-gray-900"><?php echo (int)$s['enrolled_count']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="font-semibold text-green-600"><?php echo (int)($s['present_count'] ?? 0); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="font-semibold text-orange-600"><?php echo (int)($s['late_count'] ?? 0); ?></span>
                                </td>
                                <td>
                                    <a href="export_csv.php?session_id=<?php echo (int)$s['id']; ?>" 
                                       class="btn btn-ghost btn-sm"
                                       data-tooltip="Exporter cette session">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-12">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-gray-300 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Aucune session programmée ce jour</p>
                    <p class="text-gray-400 text-sm mt-1">Sélectionnez une autre date pour voir les sessions</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-on-load">
            <!-- Recent Payments -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Paiements récents</h3>
                    <p class="card-subtitle">Dernières transactions d'examens</p>
                </div>
                
                <div class="card-body">
                    <?php if (count($payments) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($payments, 0, 5) as $p): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-green-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($p['type']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">
                                    <?php echo number_format((float)$p['amount'], 0, ',', ' '); ?> <?php echo htmlspecialchars($p['currency'] ?? 'CDF'); ?>
                                </div>
                                <div class="text-sm">
                                    <span class="badge badge-success"><?php echo htmlspecialchars($p['status']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-gray-300 mx-auto mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        <p class="text-gray-500">Aucun paiement récent</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Exam Authorizations -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Autorisations d'examen</h3>
                    <p class="card-subtitle">Dernières autorisations accordées</p>
                </div>
                
                <div class="card-body">
                    <?php if (count($auths) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach (array_slice($auths, 0, 5) as $a): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 <?php echo ((int)$a['allowed'] === 1) ? 'bg-green-100' : 'bg-red-100'; ?> rounded-full flex items-center justify-center">
                                    <?php if ((int)$a['allowed'] === 1): ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-green-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <?php else: ?>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-red-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">
                                        <?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($a['exam_name']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <?php if ((int)$a['allowed'] === 1): ?>
                                <span class="badge badge-success">Autorisé</span>
                                <?php else: ?>
                                <span class="badge badge-error">Refusé</span>
                                <?php endif; ?>
                                <div class="text-xs text-gray-500 mt-1">
                                    <?php echo $a['allowed_at'] ? date('d/m H:i', strtotime($a['allowed_at'])) : ''; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-8">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-gray-300 mx-auto mb-3">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-500">Aucune autorisation récente</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="assets/js/app.js"></script>
</body>
</html>

