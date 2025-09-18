<?php
declare(strict_types=1);

// Legacy compatibility - redirect to new system
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AttendanceController;

$controller = new AttendanceController();
$controller->scan();

