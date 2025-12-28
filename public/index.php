<?php
// Загружаем переменные окружения
require_once dirname(__DIR__) . '/bootstrap/environment.php';

// Загружаем хелперы ДО настройки отображения ошибок
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

// Настройка отображения ошибок
$appEnv = env('APP_ENV', 'production');
$appDebug = env('APP_DEBUG', 'false') === 'true';

if ($appDebug || $appEnv === 'development' || $appEnv === 'local') {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

// Автозагрузчик Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    // Простая страница ошибки вместо кучи кода
    echo '<!DOCTYPE html>';
    echo '<html><head><title>Composer не установлен</title></head>';
    echo '<body style="font-family: Arial; padding: 40px; text-align: center;">';
    echo '<h1>Composer не установлен</h1>';
    echo '<p>Запустите: <code>composer install</code></p>';
    echo '</body></html>';
    exit;
}

require_once $autoloader;

// Регистрируем глобальный обработчик ошибок
App\Core\ErrorHandler::register();

// Инициализируем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Запускаем приложение
try {
    $app = new App\Core\Application();
    $app->run();
} catch (\Throwable $e) {
    // Если что-то пошло не так на самом высоком уровне
    App\Core\ErrorHandler::handleException($e);
}