<?php
declare(strict_types=1);

namespace App\Core;

class Logger
{
    private static $instance;
    private $logFile;
    private $debugMode;

    private function __construct()
    {
        $this->logFile = dirname(__DIR__, 2) . '/storage/logs/app.log';
        $this->debugMode = env('APP_DEBUG', false);

        // Создаем директорию логов если ее нет
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        if ($this->debugMode) {
            $this->write('DEBUG', $message, $context);
        }
    }

    private function write(string $level, string $message, array $context): void
    {
        $logLine = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($this->logFile, $logLine, FILE_APPEND);

        // В режиме отладки также выводим в error_log
        if ($this->debugMode && $level === 'ERROR') {
            error_log(trim($logLine));
        }
    }
}