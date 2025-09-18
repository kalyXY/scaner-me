<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Config\Config;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class QrGeneratorService
{
    private Student $studentModel;
    private LoggingService $logger;
    private string $outputDir;
    private int $qrSize;
    private int $qrMargin;

    public function __construct(
        Student $studentModel = null,
        LoggingService $logger = null
    ) {
        $this->studentModel = $studentModel ?? new Student();
        $this->logger = $logger ?? new LoggingService();
        
        $this->outputDir = Config::get('qr.output_dir', __DIR__ . '/../../assets/qr');
        $this->qrSize = Config::get('qr.size', 300);
        $this->qrMargin = Config::get('qr.margin', 10);
    }

    public function generateAllStudentQrCodes(): int
    {
        $this->ensureOutputDirectory();
        
        $students = $this->studentModel->getActiveStudents();
        $writer = new PngWriter();
        $count = 0;

        foreach ($students as $student) {
            try {
                $this->generateStudentQrCode($student, $writer);
                $count++;
            } catch (\Exception $e) {
                $this->logger->error('Failed to generate QR code for student', [
                    'student_id' => $student['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->logger->info('QR code generation completed', [
            'total_generated' => $count,
            'output_directory' => $this->outputDir
        ]);

        return $count;
    }

    public function generateStudentQrCode(array $student, PngWriter $writer = null): string
    {
        if (!$writer) {
            $writer = new PngWriter();
        }

        $baseUrl = Config::get('app.url', 'http://localhost');
        $scanUrl = rtrim($baseUrl, '/') . '/api/scan?uuid=' . $student['uuid'];

        $qr = QrCode::create($scanUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize($this->qrSize)
            ->setMargin($this->qrMargin);

        $result = $writer->write($qr);
        
        $fileName = $this->generateFileName($student);
        $filePath = $this->outputDir . '/' . $fileName;
        
        $result->saveToFile($filePath);

        return $filePath;
    }

    public function generateSingleQrCode(string $uuid): ?string
    {
        $student = $this->studentModel->findByUuid($uuid);
        
        if (!$student || !$student['is_active']) {
            return null;
        }

        $this->ensureOutputDirectory();
        
        return $this->generateStudentQrCode($student);
    }

    private function generateFileName(array $student): string
    {
        $lastName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student['last_name']);
        return sprintf('%s_%s.png', $student['uuid'], $lastName);
    }

    private function ensureOutputDirectory(): void
    {
        if (!is_dir($this->outputDir)) {
            if (!mkdir($this->outputDir, 0755, true)) {
                throw new \RuntimeException("Failed to create QR output directory: {$this->outputDir}");
            }
        }

        if (!is_writable($this->outputDir)) {
            throw new \RuntimeException("QR output directory is not writable: {$this->outputDir}");
        }
    }
}