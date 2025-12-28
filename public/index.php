<?php

// Загружаем переменные окружения
require_once dirname(__DIR__) . '/bootstrap/environment.php';

// Загружаем хелперы ДО настройки отображения ошибок
require_once dirname(__DIR__) . '/bootstrap/helpers.php';

// НЕ меняем настройки ошибок - оставляем как есть
$appEnv = env('APP_ENV', 'production');
$appDebug = env('APP_DEBUG', 'false') === 'true';

// Автозагрузчик Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    echo '<!DOCTYPE html>';
    echo '<html><head><title>Composer не установлен</title></head>';
    echo '<body style="font-family: Arial; padding: 40px; text-align: center;">';
    echo '<h1>Composer не установлен</h1>';
    echo '<p>Запустите: <code>composer install</code></p>';
    echo '</body></html>';
    exit;
}

require_once $autoloader;

 App\Core\ErrorHandler::register();

// Инициализируем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Запускаем приложение С ВЫВОДОМ ОШИБОК
try {
    $app = new App\Core\Application();
    $app->run();
} catch (\Throwable $e) {
    echo '<pre style="background: #f8d7da; padding: 20px; border: 2px solid #f5c6cb; border-radius: 5px;">';
    echo '<strong>FATAL ERROR:</strong><br>';
    echo 'Message: ' . htmlspecialchars($e->getMessage()) . '<br>';
    echo 'File: ' . $e->getFile() . ':' . $e->getLine() . '<br>';
    echo 'Trace:<br>' . htmlspecialchars($e->getTraceAsString());
    echo '</pre>';
}