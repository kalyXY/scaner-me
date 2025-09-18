<?php
declare(strict_types=1);

// Vérifier si on est appelé directement ou depuis index.php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../bootstrap.php';
}

$pdo = db();

$date = $_GET['date'] ?? null;
if (!$date) {
    $date = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d');
}

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
     LIMIT 20'
)->fetchAll();

// Recent exam authorizations
$auths = $pdo->query(
    'SELECT ea.id, s.first_name, s.last_name, e.name AS exam_name, ea.allowed, ea.allowed_at
     FROM exam_authorizations ea
     JOIN students s ON s.id = ea.student_id
     JOIN exams e ON e.id = ea.exam_id
     ORDER BY ea.id DESC
     LIMIT 20'
)->fetchAll();

?><!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - QR Attendance</title>
    <style>
      body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif; margin: 24px; color: #222; }
      h1, h2 { margin: 0 0 12px; }
      .grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
      table { border-collapse: collapse; width: 100%; }
      th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
      th { background: #f7f7f7; }
      .muted { color: #666; }
      .pill { padding: 2px 8px; border-radius: 999px; font-size: 12px; }
      .pill.exam { background: #fdecea; color: #b0422a; }
      .pill.class { background: #eef5ff; color: #1e4fa3; }
      .toolbar { display: flex; gap: 8px; align-items: center; margin-bottom: 12px; }
      input[type=date] { padding: 6px 8px; }
      a.button { display: inline-block; background: #1f6feb; color: #fff; padding: 8px 10px; border-radius: 6px; text-decoration: none; }
    </style>
  </head>
  <body>
    <h1>Tableau de bord</h1>
    <div class="toolbar">
      <form method="get">
        <label for="date">Date: </label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>">
        <button type="submit">Afficher</button>
      </form>
      <a class="button" href="export_csv.php?date=<?php echo urlencode($date); ?>&scope=today">Exporter CSV (jour)</a>
    </div>

    <div class="grid">
      <section>
        <h2>Présence par session (<?php echo htmlspecialchars($date); ?>)</h2>
        <table>
          <thead>
            <tr>
              <th>Heure</th>
              <th>Classe</th>
              <th>Cours</th>
              <th>Type</th>
              <th>Inscrits</th>
              <th>Présents</th>
              <th>Retards</th>
              <th>Scans</th>
              <th>Détails</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sessions as $s): ?>
              <tr>
                <td><?php echo htmlspecialchars($s['start_time'] . ' - ' . ($s['end_time'] ?? '')); ?></td>
                <td><span class="pill class"><?php echo htmlspecialchars($s['class_name'] ?? '-'); ?></span></td>
                <td><?php echo htmlspecialchars($s['course_name']); ?></td>
                <td><?php echo ((int)$s['is_exam'] === 1) ? '<span class="pill exam">Examen</span>' : 'Cours'; ?></td>
                <td><?php echo (int)$s['enrolled_count']; ?></td>
                <td><?php echo (int)($s['present_count'] ?? 0); ?></td>
                <td><?php echo (int)($s['late_count'] ?? 0); ?></td>
                <td><?php echo (int)($s['scanned_count'] ?? 0); ?></td>
                <td><a href="export_csv.php?session_id=<?php echo (int)$s['id']; ?>">Exporter CSV</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($sessions) === 0): ?>
              <tr><td colspan="9" class="muted">Aucune session ce jour.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <section>
        <h2>Paiements récents</h2>
        <table>
          <thead>
            <tr>
              <th>Étudiant</th>
              <th>Type</th>
              <th>Montant</th>
              <th>Devise</th>
              <th>Statut</th>
              <th>Payé le</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                <td><?php echo htmlspecialchars($p['type']); ?></td>
                <td><?php echo htmlspecialchars(number_format((float)$p['amount'], 2)); ?></td>
                <td><?php echo htmlspecialchars($p['currency'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($p['status']); ?></td>
                <td><?php echo htmlspecialchars($p['paid_at'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($payments) === 0): ?>
              <tr><td colspan="6" class="muted">Aucun paiement.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>

      <section>
        <h2>Autorisations d'examen récentes</h2>
        <table>
          <thead>
            <tr>
              <th>Étudiant</th>
              <th>Examen</th>
              <th>Autorisé</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($auths as $a): ?>
              <tr>
                <td><?php echo htmlspecialchars($a['first_name'] . ' ' . $a['last_name']); ?></td>
                <td><?php echo htmlspecialchars($a['exam_name']); ?></td>
                <td><?php echo ((int)$a['allowed'] === 1) ? 'Oui' : 'Non'; ?></td>
                <td><?php echo htmlspecialchars($a['allowed_at'] ?? ''); ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (count($auths) === 0): ?>
              <tr><td colspan="4" class="muted">Aucune autorisation.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </div>
  </body>
</html>

