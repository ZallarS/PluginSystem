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