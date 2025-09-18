<?php
declare(strict_types=1);

// Vérifier si on est appelé directement ou depuis index.php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../bootstrap.php';
}

use App\Controllers\AttendanceController;

$controller = new AttendanceController();
$controller->scan();

