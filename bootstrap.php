<?php
/**
 * Bootstrap de l'application - PHP Natif 8.0.30
 * Compatible XAMPP MySQL
 */

// Chargement de la configuration
require_once __DIR__ . '/config_native.php';

// Chargement de l'autoloader natif
require_once __DIR__ . '/autoload.php';

// Démarrage de la session si nécessaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des headers de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Initialisation de la base de données (test de connexion)
try {
    $db = App\Config\Database::getConnection();
    // Test simple de connexion
    $db->query('SELECT 1');
} catch (Exception $e) {
    if (APP_ENV === 'development') {
        die('Erreur de connexion à la base de données: ' . $e->getMessage() . '<br>Vérifiez que XAMPP MySQL est démarré.');
    } else {
        die('Erreur de connexion à la base de données');
    }
}

// L'application est maintenant prête à être utilisée