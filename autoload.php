<?php
/**
 * Simple PHP Native Autoloader
 * Compatible avec PHP 8.0.30
 */

spl_autoload_register(function ($className) {
    // Convertir les namespaces en chemins de fichiers
    $className = ltrim($className, '\\');
    $fileName = '';
    $namespace = '';
    
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    
    // Chercher dans le dossier src/ pour les classes App\
    if (strpos($fileName, 'App' . DIRECTORY_SEPARATOR) === 0) {
        $fileName = 'src' . DIRECTORY_SEPARATOR . substr($fileName, 4);
    }
    
    $fullPath = __DIR__ . DIRECTORY_SEPARATOR . $fileName;
    
    if (file_exists($fullPath)) {
        require $fullPath;
    }
});

// Charger les fonctions utilitaires
require_once __DIR__ . '/src/Utils/helpers.php';