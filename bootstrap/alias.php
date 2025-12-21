<?php
// bootstrap/alias.php - Безопасные алиасы

// Отключаем предупреждения для class_alias временно
$old_error_level = error_reporting();
error_reporting($old_error_level & ~E_WARNING);

// Функция для безопасного создания алиаса
function safe_class_alias($original, $alias) {
    if (class_exists($original, false) && !class_exists($alias, false)) {
        return class_alias($original, $alias);
    }
    return false;
}

// 1. Создаем алиасы для ядра системы
safe_class_alias('App\Core\Application', 'Core\Core');
safe_class_alias('App\Core\Container\Container', 'Core\Container\Container');
safe_class_alias('App\Core\View\TemplateEngine', 'Core\TemplateEngine');
safe_class_alias('App\Core\HookManager', 'Core\HookManager');
safe_class_alias('App\Core\Routing\Router', 'Core\Router\Router');
safe_class_alias('App\Core\Routing\Route', 'Core\Router\Route');
safe_class_alias('App\Core\Widgets\WidgetManager', 'Core\Widgets\WidgetManager');

// 2. Специальная обработка для плагинов
if (class_exists('Plugins\PluginManager', false)) {
    safe_class_alias('Plugins\PluginManager', 'Core\Plugins\PluginManager');
}

// 3. Автозагрузчик для старых классов на случай, если их запросят
spl_autoload_register(function ($className) {
    // Маппинг старых имен на новые
    $mapping = [
        'Core\Core' => 'App\Core\Application',
        'Core\Container\Container' => 'App\Core\Container\Container',
        'Core\TemplateEngine' => 'App\Core\View\TemplateEngine',
        'Core\HookManager' => 'App\Core\HookManager',
        'Core\Router\Router' => 'App\Core\Routing\Router',
        'Core\Router\Route' => 'App\Core\Routing\Route',
        'Core\Widgets\WidgetManager' => 'App\Core\Widgets\WidgetManager',
        'App\Controllers\BaseController' => 'App\Http\Controllers\Controller',
        'App\Controllers\HomeController' => 'App\Http\Controllers\HomeController',
        'App\Controllers\AdminController' => 'App\Http\Controllers\AdminController',
        'App\Controllers\AuthController' => 'App\Http\Controllers\AuthController',
        'App\Controllers\PluginController' => 'App\Http\Controllers\PluginController',
    ];

    if (isset($mapping[$className])) {
        $newClassName = $mapping[$className];
        if (class_exists($newClassName, false)) {
            class_alias($newClassName, $className);
            return true;
        }
    }

    // Попробуем загрузить из старой структуры как последнее средство
    $oldPaths = [
        __DIR__ . '/../core/' . str_replace('\\', '/', $className) . '.php',
        __DIR__ . '/../app/Controllers/' . basename(str_replace('\\', '/', $className)) . '.php',
    ];

    foreach ($oldPaths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
    }

    return false;
}, true, true);

// Восстанавливаем уровень ошибок
error_reporting($old_error_level);