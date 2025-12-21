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
            $auth = auth();
            if (method_exists($auth, 'getCsrfToken')) {
                return $auth->getCsrfToken();
            }
        } catch (Exception $e) {
            error_log("csrf_token(): Error: " . $e->getMessage());
        }

        // Fallback
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
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
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session($key = null, $value = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            require_once dirname(__DIR__) . '/bootstrap/session.php';
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
    function template($template, $data = [])
    {
        static $engine = null;

        if ($engine === null) {
            $engine = new App\Core\View\TemplateEngine();
        }

        return $engine->render($template, $data);
    }
}

if (!function_exists('render')) {
    function render($template, $data = [])
    {
        echo template($template, $data);
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
if (!function_exists('app')) {
    /**
     * Получить экземпляр контейнера или конкретный сервис
     */
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
                if ($container->has($abstract)) {
                    return $container->get($abstract);
                }

                // Попробуем создать класс, если он существует
                if (class_exists($abstract)) {
                    return $container->make($abstract);
                }
            }

            return null;

        } catch (Exception $e) {
            error_log("app(): Error: " . $e->getMessage());
            return null;
        }
    }
}
if (!function_exists('auth')) {
    /**
     * Получить сервис аутентификации
     */
    function auth()
    {
        try {
            $authService = app(\App\Services\AuthService::class);

            if ($authService) {
                return $authService;
            }
        } catch (Exception $e) {
            error_log("auth(): Error getting AuthService: " . $e->getMessage());
        }

        // Fallback для обратной совместимости
        return new class {
            public function isLoggedIn() {
                return isset($_SESSION['user_id']) &&
                    isset($_SESSION['is_admin']) &&
                    $_SESSION['is_admin'];
            }

            public function getCsrfToken() {
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                return $_SESSION['csrf_token'];
            }

            public function validateCsrfToken($token) {
                return isset($_SESSION['csrf_token']) &&
                    hash_equals($_SESSION['csrf_token'], $token);
            }

            public function getCurrentUser() {
                if (!$this->isLoggedIn()) {
                    return null;
                }

                // Создаем минимальный объект пользователя
                return new class {
                    public function getId() { return $_SESSION['user_id'] ?? null; }
                    public function getUsername() { return $_SESSION['username'] ?? 'admin'; }
                    public function isAdmin() { return true; }
                };
            }
        };
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
    /**
     * Рендеринг шаблона
     */
    function view($template, $data = [])
    {
        $engine = new \App\Core\View\TemplateEngine();
        return $engine->render($template, $data);
    }
}
if (!function_exists('config')) {
    /**
     * Получить значение конфигурации
     */
    function config($key, $default = null)
    {
        static $configs = [];

        $parts = explode('.', $key);
        $file = $parts[0];

        if (!isset($configs[$file])) {
            $configPath = config_path("{$file}.php");
            if (file_exists($configPath)) {
                $configs[$file] = require $configPath;
            } else {
                $configs[$file] = [];
            }
        }

        $config = $configs[$file];

        // Убираем первый элемент (название файла)
        array_shift($parts);

        // Ищем значение по пути
        foreach ($parts as $part) {
            if (isset($config[$part])) {
                $config = $config[$part];
            } else {
                return $default;
            }
        }

        return $config;
    }
}