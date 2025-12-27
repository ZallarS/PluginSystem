<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ErrorHandler class
 *
 * Handles PHP errors and exceptions in the application.
 * Logs errors and displays appropriate error pages.
 *
 * @package App\Core
 */
class ErrorHandler
{
    /**
     * @var Logger The logger instance
     */
    private Logger $logger;

    /**
     * @var bool Whether debug mode is enabled
     */
    private bool $debug;


    /**
     * Create a new error handler instance.
     *
     * @param Logger $logger The logger instance
     * @param bool $debug Whether debug mode is enabled
     */
    public function __construct(Logger $logger, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;

        if (!$debug) {
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
        }
    }

    /**
     * Handle a PHP error.
     *
     * Logs the error and optionally throws an exception in debug mode.
     *
     * @param int $level The error level
     * @param string $message The error message
     * @param string $file The file where the error occurred
     * @param int $line The line number where the error occurred
     * @return bool True to prevent PHP's internal error handler from running
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            $this->logger->error($message, ['file' => $file, 'line' => $line]);

            if ($this->debug) {
                throw new \ErrorException($message, 0, $level, $file, $line);
            }
        }

        return true;
    }

    /**
     * Handle an uncaught exception.
     *
     * Logs the exception and displays an error page.
     *
     * @param \Throwable $e The uncaught exception
     * @return void
     */
    public function handleException(\Throwable $e): void
    {
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($this->debug) {
            throw $e;
        }

        http_response_code(500);

        $errorPage = dirname(__DIR__, 2) . '/resources/views/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Произошла внутренняя ошибка сервера.</p>";
        }

        exit(1);
    }
}
