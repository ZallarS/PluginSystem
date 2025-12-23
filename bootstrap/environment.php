<?php
// Загрузка переменных окружения из .env файла
function loadEnvironmentVariables($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Пропускаем комментарии
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Разделяем ключ и значение
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            // Удаляем кавычки
            $value = trim($value, '"\'');

            // Устанавливаем в окружение
            if (!array_key_exists($key, $_SERVER)) {
                $_SERVER[$key] = $value;
            }
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Загружаем .env файл если существует
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    loadEnvironmentVariables($envPath);
}

// Определяем константы только если они еще не определены
if (!defined('APP_ENV')) {
    define('APP_ENV', $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production');
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? 'false') === 'true');
}

if (!defined('APP_URL')) {
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
}