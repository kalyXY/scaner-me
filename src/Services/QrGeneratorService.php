<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Config\Config;

class QrGeneratorService
{
    private Student $studentModel;
    private string $outputDir;
    private int $qrSize;

    public function __construct(Student $studentModel = null)
    {
        $this->studentModel = $studentModel ?? new Student();
        
        $this->outputDir = ROOT_PATH . '/assets/qr';
        $this->qrSize = 300;
    }

    public function generateAllStudentQrCodes(): int
    {
        $this->ensureOutputDirectory();
        
        $students = $this->studentModel->getActiveStudents();
        $count = 0;

        foreach ($students as $student) {
            try {
                $this->generateStudentQrCode($student);
                $count++;
            } catch (\Exception $e) {
                logger("Erreur génération QR pour étudiant {$student['id']}: " . $e->getMessage(), 'error');
            }
        }

        logger("Génération QR terminée: $count codes générés dans {$this->outputDir}");

        return $count;
    }

    public function generateStudentQrCode(array $student): string
    {
        $scanUrl = $this->generateScanUrl($student['uuid']);
        
        $fileName = $this->generateFileName($student);
        $filePath = $this->outputDir . '/' . $fileName;
        
        // Générer le QR code via une API publique (solution simple)
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?" . http_build_query([
            'size' => $this->qrSize . 'x' . $this->qrSize,
            'data' => $scanUrl,
            'format' => 'png',
            'ecc' => 'H'  // High error correction
        ]);

        // Télécharger l'image QR
        $qrData = file_get_contents($qrApiUrl);
        
        if ($qrData === false) {
            throw new \RuntimeException("Impossible de générer le QR code pour {$student['uuid']}");
        }

        file_put_contents($filePath, $qrData);

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

    private function generateScanUrl(string $uuid): string
    {
        // URL de base configurable
        $baseUrl = 'http://localhost/qr-attendance/public';
        return $baseUrl . '/api/attendance?uuid=' . $uuid;
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