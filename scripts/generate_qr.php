<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Services\QrGeneratorService;

try {
    $qrService = new QrGeneratorService();
    $count = $qrService->generateAllStudentQrCodes();
    
    echo "✅ Generated {$count} QR codes successfully!\n";
    echo "📁 Output directory: assets/qr/\n";
} catch (Exception $e) {
    echo "❌ Error generating QR codes: " . $e->getMessage() . "\n";
    exit(1);
}

