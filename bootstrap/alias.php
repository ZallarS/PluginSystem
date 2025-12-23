<?php
// Удаляем сложную логику автозагрузки, оставляем только алиасы

function safe_class_alias($original, $alias) {
    if (class_exists($original, false) && !class_exists($alias, false)) {
        return class_alias($original, $alias);
    }
    return false;
}

// Только самые необходимые алиасы для обратной совместимости
safe_class_alias('App\Core\Application', 'Core\Core');
safe_class_alias('App\Core\Widgets\WidgetManager', 'Core\Widgets\WidgetManager');