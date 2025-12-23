<?php
declare(strict_types=1);

namespace App\Core;

class ErrorHandler
{
    private Logger $logger;
    private bool $debug;

    public function __construct(Logger $logger, bool $debug = false)
    {
        $this->logger = $logger;
        $this->debug = $debug;

        if (!$debug) {
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
        }
    }

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