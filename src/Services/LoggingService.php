<?php

declare(strict_types=1);

namespace App\Services;

class LoggingService
{
    private string $name;
    private string $logPath;

    public function __construct(string $name = 'attendance_system')
    {
        $this->name = $name;
        $this->logPath = ROOT_PATH . '/logs/app.log';
        $this->ensureLogDirectory();
    }

    private function ensureLogDirectory(): void
    {
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    private function writeLog(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$this->name}.{$level}: {$message}{$contextStr}" . PHP_EOL;
        
        // Écrire dans le fichier de log
        file_put_contents($this->logPath, $logMessage, FILE_APPEND | LOCK_EX);
        
        // En développement, afficher aussi dans error_log
        if (APP_ENV === 'development') {
            error_log("{$level}: {$message}{$contextStr}");
        }
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->writeLog('EMERGENCY', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->writeLog('ALERT', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->writeLog('CRITICAL', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->writeLog('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->writeLog('WARNING', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->writeLog('NOTICE', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->writeLog('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        if (APP_ENV === 'development') {
            $this->writeLog('DEBUG', $message, $context);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->writeLog(strtoupper($level), $message, $context);
    }
}