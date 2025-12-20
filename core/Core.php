<?php

namespace Core;

class Core
{
    private static $instance;
    private $router;
    private $config = [];
    private $pluginManager;

    public function __construct()
    {
        self::$instance = $this;
        $this->loadConfig();
        $this->initRouter();
        $this->initPlugins();
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    private function loadConfig()
    {
        // Загрузка базовой конфигурации
        $configPath = __DIR__ . '/../app/Config/app.php';
        if (file_exists($configPath)) {
            $this->config = require $configPath;
        } else {
            $this->config = [
                'name' => 'MVC System',
                'debug' => true,
                'plugins_enabled' => true
            ];
        }
    }

    private function initRouter()
    {
        $this->router = new Router\Router();

        // Базовые маршруты
        $this->router->get('/', 'App\\Controllers\\HomeController@index');
        $this->router->get('/admin', 'App\\Controllers\\AdminController@dashboard');

        // Аутентификация
        $this->router->get('/login', 'App\\Controllers\\AuthController@login');
        $this->router->post('/login', 'App\\Controllers\\AuthController@login');
        $this->router->get('/logout', 'App\\Controllers\\AuthController@logout');
        $this->router->get('/quick-login', 'App\\Controllers\\AuthController@quickLogin');

        // Управление плагинами
        $this->router->get('/admin/plugins', 'App\\Controllers\\PluginController@index');
        $this->router->post('/admin/plugins/activate/{pluginName}', 'App\\Controllers\\PluginController@activate');
        $this->router->post('/admin/plugins/deactivate/{pluginName}', 'App\\Controllers\\PluginController@deactivate');

        // ПРОСТЕЙШИЙ ТЕСТОВЫЙ МАРШРУТ
        $this->router->get('/simple-test', function() {
            echo "SIMPLE TEST WORKS!";
            exit;
        });

        error_log("Base routes registered");
    }

    private function initPlugins()
    {
        if (!($this->config['plugins_enabled'] ?? true)) {
            return;
        }

        // Создаем менеджер плагинов
        $this->pluginManager = \Plugins\PluginManager::getInstance();

        // Загружаем плагины
        $pluginsLoaded = $this->pluginManager->loadPlugins();
        error_log("Plugins loaded: " . $pluginsLoaded);

        // Получаем все плагины
        $plugins = $this->pluginManager->getActivePlugins();

        // Для каждого плагина вызываем метод registerRoutes если он существует
        foreach ($plugins as $pluginName => $plugin) {
            if (method_exists($plugin, 'registerRoutes')) {
                $plugin->registerRoutes($this->router);
                error_log("Plugin {$pluginName}: routes registered via registerRoutes method");
            }
        }
    }

    public function run()
    {
        error_log("=== DEBUG ===");
        error_log("Total routes: " . count($this->router->getRoutes()));
        foreach ($this->router->getRoutes() as $route) {
            error_log("Route: " . $route->getMethod() . " " . $route->getUri());
        }

        $this->router->dispatch();
    }

    private function handleException($exception)
    {
        http_response_code(500);

        if ($this->config['debug'] ?? true) {
            echo "<h1>Error: " . $exception->getMessage() . "</h1>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            echo "An error occurred. Please try again later.";
        }

        error_log("Core Exception: " . $exception->getMessage());
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }
}