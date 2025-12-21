<?php

    declare(strict_types=1);

    // Основные константы приложения
    define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
    define('APP_DEBUG', ($_ENV['APP_DEBUG'] ?? 'false') === 'true');
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');

    // Пути (для обратной совместимости)
    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');
    define('CONFIG_PATH', BASE_PATH . '/config');
    define('STORAGE_PATH', BASE_PATH . '/storage');
    define('PUBLIC_PATH', BASE_PATH . '/public');
    define('RESOURCES_PATH', BASE_PATH . '/resources');
    define('PLUGINS_PATH', BASE_PATH . '/plugins');

    // Константы для окружения
    define('ENV_PRODUCTION', 'production');
    define('ENV_STAGING', 'staging');
    define('ENV_LOCAL', 'local');
    define('ENV_TESTING', 'testing');

    // Временные константы для обратной совместимости
    if (!defined('CORE_PATH')) {
        define('CORE_PATH', BASE_PATH . '/core'); // Для плагинов, которые еще используют старые пути
    }