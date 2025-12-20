<?php
// Включаем отображение ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Для отладки - логируем начало выполнения
error_log("=== MVC System Started ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A'));

// Автозагрузчик Composer
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    die('<h1>Composer не установлен</h1><p>Запустите: <code>composer install</code></p>');
}

require_once $autoloader;

// Регистрируем автозагрузчик для плагинов
spl_autoload_register(function ($class) {
    // Если класс начинается с Plugins\
    if (strpos($class, 'Plugins\\') === 0) {
        $classPath = str_replace('\\', '/', $class);
        $file = __DIR__ . '/../' . $classPath . '.php';

        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    // Также пробуем загрузить из папки plugins
    $pluginsDir = __DIR__ . '/../plugins/';
    $classParts = explode('\\', $class);
    $className = end($classParts);

    // Пробуем найти файл Plugin.php в папке с именем класса
    $possiblePaths = [
        $pluginsDir . $class . '/Plugin.php',
        $pluginsDir . str_replace('\\', '/', $class) . '/Plugin.php',
        $pluginsDir . $className . '/Plugin.php',
    ];

    foreach ($possiblePaths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});

// Начинаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Запускаем приложение
try {
    $core = new Core\Core();
    $core->run();
} catch (Throwable $e) {
    error_log("Fatal Error: " . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";

    // В режиме разработки показываем детали
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}