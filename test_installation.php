<?php
/**
 * Script de test pour vérifier l'installation
 * À exécuter depuis le navigateur : http://localhost/qr-attendance/test_installation.php
 */

echo "<h1>Test d'installation - QR Attendance System</h1>";
echo "<hr>";

// Test 1: Version PHP
echo "<h2>1. Version PHP</h2>";
$phpVersion = phpversion();
echo "Version PHP actuelle: <strong>$phpVersion</strong><br>";
if (version_compare($phpVersion, '8.0.0', '>=')) {
    echo "<span style='color: green'>✅ PHP 8.0+ détecté</span><br>";
} else {
    echo "<span style='color: red'>❌ PHP 8.0+ requis</span><br>";
}

// Test 2: Extensions PHP
echo "<h2>2. Extensions PHP</h2>";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green'>✅ $ext</span><br>";
    } else {
        echo "<span style='color: red'>❌ $ext manquante</span><br>";
    }
}

// Test 3: Configuration
echo "<h2>3. Configuration</h2>";
try {
    require_once __DIR__ . '/config_native.php';
    echo "<span style='color: green'>✅ Fichier de configuration chargé</span><br>";
    echo "Base de données: <strong>" . DB_NAME . "</strong><br>";
    echo "Hôte: <strong>" . DB_HOST . ":" . DB_PORT . "</strong><br>";
    echo "Utilisateur: <strong>" . DB_USER . "</strong><br>";
} catch (Exception $e) {
    echo "<span style='color: red'>❌ Erreur de configuration: " . $e->getMessage() . "</span><br>";
}

// Test 4: Autoloader
echo "<h2>4. Autoloader</h2>";
try {
    require_once __DIR__ . '/autoload.php';
    echo "<span style='color: green'>✅ Autoloader chargé</span><br>";
} catch (Exception $e) {
    echo "<span style='color: red'>❌ Erreur autoloader: " . $e->getMessage() . "</span><br>";
}

// Test 5: Connexion base de données
echo "<h2>5. Connexion base de données</h2>";
try {
    $db = App\Config\Database::getConnection();
    $result = $db->query("SELECT 1 as test")->fetch();
    if ($result['test'] == 1) {
        echo "<span style='color: green'>✅ Connexion MySQL réussie</span><br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red'>❌ Erreur de connexion: " . $e->getMessage() . "</span><br>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL est démarré</li>";
    echo "<li>La base de données '" . DB_NAME . "' existe</li>";
    echo "<li>Les paramètres de connexion sont corrects</li>";
    echo "</ul>";
}

// Test 6: Tables de la base de données
echo "<h2>6. Tables de la base de données</h2>";
try {
    $db = App\Config\Database::getConnection();
    $tables = ['students', 'courses', 'course_sessions', 'attendance', 'payments', 'exam_authorizations'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($result) {
            echo "<span style='color: green'>✅ Table $table</span><br>";
        } else {
            echo "<span style='color: orange'>⚠️ Table $table manquante</span><br>";
        }
    }
} catch (Exception $e) {
    echo "<span style='color: red'>❌ Erreur lors de la vérification des tables</span><br>";
}

// Test 7: Fonctions utilitaires
echo "<h2>7. Fonctions utilitaires</h2>";
if (function_exists('responseJson')) {
    echo "<span style='color: green'>✅ Fonction responseJson</span><br>";
} else {
    echo "<span style='color: red'>❌ Fonction responseJson manquante</span><br>";
}

if (function_exists('db')) {
    echo "<span style='color: green'>✅ Fonction db</span><br>";
} else {
    echo "<span style='color: red'>❌ Fonction db manquante</span><br>";
}

// Test 8: Permissions de fichiers
echo "<h2>8. Permissions</h2>";
if (is_readable(__DIR__ . '/src')) {
    echo "<span style='color: green'>✅ Dossier src lisible</span><br>";
} else {
    echo "<span style='color: red'>❌ Dossier src non lisible</span><br>";
}

if (is_readable(__DIR__ . '/public')) {
    echo "<span style='color: green'>✅ Dossier public lisible</span><br>";
} else {
    echo "<span style='color: red'>❌ Dossier public non lisible</span><br>";
}

echo "<hr>";
echo "<h2>Liens de test</h2>";
echo "<a href='public/'>🏠 Accueil / Dashboard</a><br>";
echo "<a href='public/scanner'>📱 Scanner QR</a><br>";
echo "<a href='public/api/health' target='_blank'>🔧 API Health Check</a><br>";

echo "<hr>";
echo "<p><strong>Si tous les tests sont verts, votre installation est prête !</strong></p>";
echo "<p>Si vous avez des erreurs, consultez le fichier INSTALLATION.md</p>";
?>