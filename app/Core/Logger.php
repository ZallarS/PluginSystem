<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Logger class
 *
 * A simple logging implementation that writes messages to a file.
 * Supports different log levels and context information.
 *
 * @package App\Core
 */
class Logger
{
    /**
     * @var self|null The singleton instance
     */
    private static $instance;

    /**
     * @var string The path to the log file
     */
    private $logFile;

    /**
     * @var bool Whether debug mode is enabled
     */
    private $debugMode;


    /**
     * Create a new logger instance.
     *
     * Private constructor to enforce singleton pattern.
     * Initializes the log file path and debug mode.
     */
    private function __construct()
    {
        $this->logFile = storage_path('logs/app.log');
        $this->debugMode = env('APP_DEBUG', false);

        // Создаем директорию логов если ее нет
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Get the singleton instance of the logger.
     *
     * @return self The logger instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an error message.
     *
     * @param string $message The error message
     * @param array $context Additional context information
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The warning message
     * @param array $context Additional context information
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    /**
     * Log an informational message.
     *
     * @param string $message The information message
     * @param array $context Additional context information
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    /**
     * Log a debug message.
     *
     * Only logs messages when debug mode is enabled.
     *
     * @param string $message The debug message
     * @param array $context Additional context information
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->debugMode) {
            $this->write('DEBUG', $message, $context);
        }
    }

    /**
     * Write a log message to the file.
     *
     * @param string $level The log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message The log message
     * @param array $context Additional context information
     * @return void
     */
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

    }
}
