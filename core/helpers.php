<?php

if (!function_exists('env')) {
    /**
     * Получает значение переменной окружения
     */
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        return $value;
    }
}

if (!function_exists('config_path')) {
    /**
     * Получает путь к конфигурации
     */
    function config_path($path = '')
    {
        return __DIR__ . '/../app/Config' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('app_path')) {
    /**
     * Получает путь к приложению
     */
    function app_path($path = '')
    {
        return __DIR__ . '/../app' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Получает путь к хранилищу
     */
    function storage_path($path = '')
    {
        return __DIR__ . '/../storage' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Получает путь к публичной директории
     */
    function public_path($path = '')
    {
        return __DIR__ . '/../public' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('plugins_path')) {
    /**
     * Получает путь к плагинам
     */
    function plugins_path($path = '')
    {
        return __DIR__ . '/../plugins' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('themes_path')) {
    /**
     * Получает путь к темам
     */
    function themes_path($path = '')
    {
        return __DIR__ . '/../themes' . ($path ? '/' . $path : '');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(...$args)
    {
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('abort')) {
    /**
     * Выбрасывает исключение с HTTP кодом
     */
    function abort($code, $message = '')
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('asset')) {
    /**
     * Генерирует URL для ассета
     */
    function asset($path)
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Генерирует URL
     */
    function url($path = '')
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    /**
     * Генерирует URL для маршрута
     */
    function route($name, $params = [])
    {
        // TODO: Реализовать генерацию URL по имени маршрута
        return url($name);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Генерирует CSRF токен
     */
    function csrf_token()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
if (!function_exists('csrf_field')) {
    /**
     * Генерирует поле CSRF
     */
    function csrf_field()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Возвращает старое значение из сессии
     */
    function old($key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    /**
     * Работа с сессией
     */
    function session($key = null, $value = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($key === null) {
            return $_SESSION;
        }

        if ($value === null) {
            return $_SESSION[$key] ?? null;
        }

        $_SESSION[$key] = $value;
    }
}

if (!function_exists('flash')) {
    /**
     * Флеш-сообщения
     */
    function flash($key, $value = null)
    {
        if ($value === null) {
            $message = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $message;
        }

        $_SESSION['flash'][$key] = $value;
    }
}
if (!function_exists('template')) {
    /**
     * Хелпер для работы с шаблонами
     */
    function template($template, $data = [])
    {
        static $engine = null;

        if ($engine === null) {
            $engine = new Core\TemplateEngine();
        }

        return $engine->render($template, $data);
    }
}

if (!function_exists('render')) {
    /**
     * Быстрый рендеринг шаблона
     */
    function render($template, $data = [])
    {
        echo template($template, $data);
    }
}
if (!function_exists('add_action')) {
    /**
     * Добавить действие (хук)
     */
    function add_action($hook, $callback, $priority = 10)
    {
        $hookManager = \Core\HookManager::getInstance();
        $hookManager->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    /**
     * Выполнить действие (хук)
     */
    function do_action($hook, ...$args)
    {
        $hookManager = \Core\HookManager::getInstance();
        $hookManager->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    /**
     * Добавить фильтр
     */
    function add_filter($filter, $callback, $priority = 10)
    {
        $hookManager = \Core\HookManager::getInstance();
        $hookManager->addFilter($filter, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    /**
     * Применить фильтр
     */
    function apply_filters($filter, $value, ...$args)
    {
        $hookManager = \Core\HookManager::getInstance();
        return $hookManager->applyFilter($filter, $value, ...$args);
    }
}

if (!function_exists('has_action')) {
    /**
     * Проверить наличие действия
     */
    function has_action($hook)
    {
        $hookManager = \Core\HookManager::getInstance();
        return $hookManager->hasAction($hook);
    }
}

if (!function_exists('has_filter')) {
    /**
     * Проверить наличие фильтра
     */
    function has_filter($filter)
    {
        $hookManager = \Core\HookManager::getInstance();
        return $hookManager->hasFilter($filter);
    }
}