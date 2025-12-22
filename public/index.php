<?php
// Определяем среду выполнения
define('APP_ENV', $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production');
define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'false') === 'true');

// Настройка отображения ошибок в зависимости от среды
if (APP_DEBUG || APP_ENV === 'development' || APP_ENV === 'local') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);

    // Включаем логгирование ошибок
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/error.log');
}

// Загружаем переменные окружения
require_once dirname(__DIR__) . '/bootstrap/environment.php';

// Автозагрузчик Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    die('<h1>Composer не установлен</h1><p>Запустите: <code>composer install</code></p>');
}

require_once $autoloader;

// Загружаем хелперы
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE && !defined('SESSION_STARTED_BY_MIDDLEWARE')) {
    // Базовые настройки безопасности
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');

    session_start();
}

// Запускаем приложение
try {
    $app = new App\Core\Application();
    $app->run();
} catch (Throwable $e) {
    error_log("Fatal Error: " . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);

    if (APP_DEBUG) {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // Показываем общую страницу ошибки
        $errorPage = __DIR__ . '/../resources/views/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Произошла внутренняя ошибка сервера.</p>";
        }
    }
}