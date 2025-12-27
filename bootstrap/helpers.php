<?php
declare(strict_types=1);

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        // 1. Проверяем $_ENV
        if (isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }
        // 2. Проверяем $_SERVER
        elseif (isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }
        // 3. Проверяем getenv()
        elseif (($value = getenv($key)) !== false) {
            // getenv уже вернул значение
        }
        // 4. Возвращаем default
        else {
            return $default;
        }

        // Преобразуем строковые значения
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

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string {
        return dirname(__DIR__) . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string {
        return base_path('app' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string {
        return base_path('config' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string {
        return base_path('storage' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string {
        return base_path('public' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('plugins_path')) {
    function plugins_path(string $path = ''): string {
        return base_path('plugins' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('themes_path')) {
    function themes_path(string $path = ''): string {
        return base_path('themes' . ($path ? '/' . ltrim($path, '/') : ''));
    }
}

if (!function_exists('dd')) {
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
    function asset($path)
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('route')) {
    function route($name, $params = [])
    {
        return url($name);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        try {
            /** @var \App\Services\AuthService $authService */
            $authService = app(\App\Services\AuthService::class);
            return $authService->getCsrfToken();
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

// Добавляем функцию для AJAX запросов:
if (!function_exists('csrf_meta')) {
    function csrf_meta()
    {
        return '<meta name="csrf-token" content="' . htmlspecialchars(csrf_token()) . '">';
    }
}

// Добавляем функцию для заголовков:
if (!function_exists('csrf_header')) {
    function csrf_header()
    {
        return ['X-CSRF-TOKEN' => csrf_token()];
    }
}

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        $session = app(\App\Core\Session\SessionInterface::class);
        return $session->get('old_input.' . $key, $default);
    }
}

if (!function_exists('session')) {
    function session($key = null, $value = null)
    {
        /** @var \App\Core\Session\SessionInterface $session */
        $session = app(\App\Core\Session\SessionInterface::class);

        if (is_null($key)) {
            return $session->all();
        }

        if (is_null($value)) {
            return $session->get($key);
        }

        $session->set($key, $value);
    }
}

if (!function_exists('flash')) {
    function flash($key, $value = null)
    {
        /** @var \App\Core\Session\SessionInterface $session */
        $session = app(\App\Core\Session\SessionInterface::class);

        if (is_null($value)) {
            return $session->getFlash($key);
        }

        $session->flash($key, $value);
    }
}

if (!function_exists('template')) {
    function template($template, $data = [])
    {
        return view($template, $data);
    }
}

if (!function_exists('render')) {
    function render($template, $data = [])
    {
        echo view($template, $data);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10)
    {
        $hookManager = App\Core\HookManager::getInstance();
        $hookManager->addAction($hook, $callback, $priority);
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args)
    {
        $hookManager = App\Core\HookManager::getInstance();
        $hookManager->doAction($hook, ...$args);
    }
}

if (!function_exists('add_filter')) {
    function add_filter($filter, $callback, $priority = 10)
    {
        $hookManager = App\Core\HookManager::getInstance();
        $hookManager->addFilter($filter, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($filter, $value, ...$args)
    {
        $hookManager = App\Core\HookManager::getInstance();
        return $hookManager->applyFilter($filter, $value, ...$args);
    }
}

if (!function_exists('has_action')) {
    function has_action($hook)
    {
        $hookManager = App\Core\HookManager::getInstance();
        return $hookManager->hasAction($hook);
    }
}

if (!function_exists('has_filter')) {
    function has_filter($filter)
    {
        $hookManager = App\Core\HookManager::getInstance();
        return $hookManager->hasFilter($filter);
    }
}
if (!function_exists('e')) {
    function e($value, $doubleEncode = true)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}
/**
 * @deprecated Используйте внедрение зависимостей вместо этой функции
 */
if (!function_exists('app')) {
    function app($abstract = null)
    {

        try {
            // Пытаемся получить Application
            $application = \App\Core\Application::getInstance();

            if (!$application) {
                // Если Application не создан, создаем его (для консольных команд)
                $application = new \App\Core\Application();
            }

            $container = $application->getContainer();

            if (is_null($abstract)) {
                return $container;
            }

            // Если запрашивается конкретный сервис
            if (is_string($abstract)) {
                // Пытаемся получить из контейнера
                try {
                    return $container->get($abstract);
                } catch (Exception $e) {
                    // Если не найден в контейнере, пробуем создать
                    if (class_exists($abstract)) {
                        return $container->make($abstract);
                    }
                }
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }
}
if (!function_exists('auth')) {
    function auth()
    {
        return app(\App\Services\AuthService::class);
    }
}
if (!function_exists('db')) {
    /**
     * Получить PDO соединение
     */
    function db()
    {
        return app(PDO::class);
    }
}
if (!function_exists('view')) {
    function view($template, $data = [])
    {
        $engine = \App\Core\View\TemplateEngine::getInstance();
        return $engine->render($template, $data);
    }
}
if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        /** @var \App\Services\ConfigService $config */
        static $config = null;

        if ($config === null) {
            $config = app(\App\Services\ConfigService::class);
        }

        if ($key === null) {
            return $config->all();
        }

        return $config->get($key, $default);
    }
}