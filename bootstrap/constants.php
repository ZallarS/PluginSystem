<?php
declare(strict_types=1);

// Основные константы приложения (только если не определены в environment.php)
if (!defined('APP_ENV')) {
    define('APP_ENV', 'production');
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (!defined('APP_URL')) {
    define('APP_URL', 'http://localhost');
}

// Пути (для обратной совместимости)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

if (!defined('APP_PATH')) {
    define('APP_PATH', BASE_PATH . '/app');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', BASE_PATH . '/config');
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', BASE_PATH . '/public');
}

if (!defined('RESOURCES_PATH')) {
    define('RESOURCES_PATH', BASE_PATH . '/resources');
}

if (!defined('PLUGINS_PATH')) {
    define('PLUGINS_PATH', BASE_PATH . '/plugins');
}

// Константы для окружения
define('ENV_PRODUCTION', 'production');
define('ENV_STAGING', 'staging');
define('ENV_LOCAL', 'local');
define('ENV_TESTING', 'testing');

// Временные константы для обратной совместимости
if (!defined('CORE_PATH')) {
    define('CORE_PATH', BASE_PATH . '/core'); // Для плагинов, которые еще используют старые пути
}