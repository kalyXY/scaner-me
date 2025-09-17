<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

$pdo = get_pdo();

$targetDir = __DIR__ . '/../assets/qr';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0775, true);
}

$stmt = $pdo->query('SELECT id, uuid, first_name, last_name FROM students WHERE is_active = 1');
$writer = new PngWriter();

$count = 0;
while ($row = $stmt->fetch()) {
    $url = sprintf('scan.php?uuid=%s', $row['uuid']);
    $qr = QrCode::create($url)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->setSize(300)
        ->setMargin(10);

    $result = $writer->write($qr);
    $fileName = sprintf('%s/%s_%s.png', $targetDir, $row['uuid'], preg_replace('/\s+/', '_', $row['last_name']));
    $result->saveToFile($fileName);
    $count++;
}

echo "Generated {$count} QR codes in assets/qr\n";

