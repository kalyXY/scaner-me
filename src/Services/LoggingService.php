<?php

declare(strict_types=1);

namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use App\Config\Config;

class LoggingService
{
    private Logger $logger;

    public function __construct(string $name = 'attendance_system')
    {
        $this->logger = new Logger($name);
        $this->setupHandlers();
    }

    private function setupHandlers(): void
    {
        $logLevel = Config::get('logging.level', Logger::INFO);
        $logPath = Config::get('logging.path', __DIR__ . '/../../logs/app.log');

        // File handler with rotation
        $fileHandler = new RotatingFileHandler($logPath, 30, $logLevel);
        $fileHandler->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        ));

        $this->logger->pushHandler($fileHandler);

        // Console handler for development
        if (Config::get('app.environment') === 'development') {
            $consoleHandler = new StreamHandler('php://stdout', $logLevel);
            $consoleHandler->setFormatter(new LineFormatter(
                "%level_name%: %message% %context%\n"
            ));
            $this->logger->pushHandler($consoleHandler);
        }
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log(int $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}