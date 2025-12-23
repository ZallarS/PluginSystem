<?php
// Загружаем переменные окружения
require_once dirname(__DIR__) . '/bootstrap/environment.php';

// Загружаем хелперы ДО настройки отображения ошибок
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

// Получаем значения окружения
$appEnv = env('APP_ENV', 'production');
$appDebug = env('APP_DEBUG', 'false') === 'true';

// Настройка отображения ошибок в зависимости от среды
if ($appDebug || $appEnv === 'development' || $appEnv === 'local') {
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

// Автозагрузчик Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    die('<h1>Composer не установлен</h1><p>Запустите: <code>composer install</code></p>');
}

require_once $autoloader;

// Запускаем приложение
try {
    $app = new App\Core\Application();
    $app->run();
} catch (Throwable $e) {
    error_log("Fatal Error: " . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);

    if ($appDebug) {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        $errorPage = __DIR__ . '/../resources/views/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Произошла внутренняя ошибка сервера.</p>";
        }
    }
}