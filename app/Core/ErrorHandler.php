<?php
declare(strict_types=1);

namespace App\Core;

/**
 * ErrorHandler class
 *
 * Global error and exception handler for the application.
 *
 * @package App\Core
 */
class ErrorHandler
{
    /**
     * Register global error handlers.
     *
     * @return void
     */
    public static function register(): void
    {
        // Устанавливаем обработчики ошибок
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);

        // Отключаем вывод ошибок в браузер (мы сами будем их обрабатывать)
        ini_set('display_errors', '0');
    }

    /**
     * Handle PHP errors.
     *
     * @param int $level The error level
     * @param string $message The error message
     * @param string $file The file where the error occurred
     * @param int $line The line number where the error occurred
     * @return bool True to prevent PHP's internal error handler from running
     */
    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        // Преобразуем ошибки в исключения
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return true;
    }

    /**
     * Handle uncaught exceptions.
     *
     * @param \Throwable $e The exception
     * @return void
     */
    public static function handleException(\Throwable $e): void
    {
        // Логируем ошибку
        self::logError($e);

        // Показываем страницу ошибки
        self::showErrorPage($e);
    }

    /**
     * Log an error.
     *
     * @param \Throwable $e The exception
     * @return void
     */
    private static function logError(\Throwable $e): void
    {
        try {
            $logFile = storage_path('logs/app.log');
            $logDir = dirname($logFile);

            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logMessage = sprintf(
                "[%s] %s: %s in %s:%s\nStack trace:\n%s\n\n",
                date('Y-m-d H:i:s'),
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } catch (\Throwable $logException) {
            // Игнорируем ошибки логгирования
        }
    }

    /**
     * Display an error page.
     *
     * @param \Throwable $e The exception
     * @return void
     */
    private static function showErrorPage(\Throwable $e): void
    {
        // Устанавливаем код ответа 500
        http_response_code(500);

        // Пытаемся загрузить шаблон ошибки
        $errorPage = base_path('resources/views/errors/500.php');
        if (file_exists($errorPage)) {
            // Передаем данные об ошибке в шаблон
            $debug = env('APP_DEBUG', false);
            require $errorPage;
        } else {
            // Фолбэк: простая страница ошибки
            self::showFallbackErrorPage($e);
        }

        exit(1);
    }

    /**
     * Display a fallback error page.
     *
     * @param \Throwable $e The exception
     * @return void
     */
    private static function showFallbackErrorPage(\Throwable $e): void
    {
        $debug = env('APP_DEBUG', false);

        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head><title>500 - Internal Server Error</title>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; padding: 40px; text-align: center; background: #f8f9fa; }';
        echo '.error-box { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
        echo 'h1 { color: #dc3545; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error-box">';
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Произошла внутренняя ошибка сервера.</p>';

        if ($debug) {
            echo '<div style="text-align: left; margin-top: 30px;">';
            echo '<h3>Debug Information:</h3>';
            echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
            echo '<pre style="background: #f8f9fa; padding: 20px; border-radius: 5px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }

        echo '</div>';
        echo '</body>';
        echo '</html>';
    }
}