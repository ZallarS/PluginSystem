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
        if (self::$instance === null) {
            self::$instance = new self();
        }
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
        $this->router->addRoute('GET', '/', 'App\\Controllers\\HomeController@index');
        $this->router->addRoute('GET', '/admin', 'App\\Controllers\\AdminController@dashboard');

        // Аутентификация
        $this->router->addRoute('GET', '/login', 'App\\Controllers\\AuthController@login');
        $this->router->addRoute('POST', '/login', 'App\\Controllers\\AuthController@login');
        $this->router->addRoute('GET', '/logout', 'App\\Controllers\\AuthController@logout');
        $this->router->addRoute('GET', '/quick-login', 'App\\Controllers\\AuthController@quickLogin');

        // Управление плагинами
        $this->router->addRoute('GET', '/admin/plugins', 'App\\Controllers\\PluginController@index');
        $this->router->addRoute('POST', '/admin/plugins/activate/{pluginName}', 'App\\Controllers\\PluginController@activate');
        $this->router->addRoute('POST', '/admin/plugins/deactivate/{pluginName}', 'App\\Controllers\\PluginController@deactivate');

        // ПРОСТЕЙШИЙ ТЕСТОВЫЙ МАРШРУТ
        $this->router->addRoute('GET', '/simple-test', function() {
            echo "SIMPLE TEST WORKS!";
            exit;
        });

        error_log("Base routes registered");
    }

    private function initPlugins()
    {
        // Загружаем конфигурацию плагинов
        $pluginsConfigPath = __DIR__ . '/../Config/plugins.php';
        if (file_exists($pluginsConfigPath)) {
            $pluginsConfig = require $pluginsConfigPath;

            if (!isset($pluginsConfig['enabled']) || !$pluginsConfig['enabled']) {
                error_log("Core: Plugins are disabled in config");
                return;
            }
        }

        error_log("Core: Initializing plugins...");

        // Создаем менеджер плагинов
        $this->pluginManager = \Plugins\PluginManager::getInstance();

        // Загружаем плагины
        $pluginsRegistered = $this->pluginManager->loadPlugins();
        error_log("Core: Plugins registered: " . $pluginsRegistered);

        // Получаем все плагины и активируем те, что должны быть активны по умолчанию
        $plugins = $this->pluginManager->getPlugins();
        error_log("Core: Total plugins: " . count($plugins));

        // Активируем плагины
        foreach ($plugins as $pluginName => $pluginData) {
            // Здесь можно добавить логику активации по умолчанию
            // Например, из базы данных или конфигурации
            if (isset($pluginData['config']['default_active']) && $pluginData['config']['default_active']) {
                $this->pluginManager->activatePlugin($pluginName);
            }
        }

        // Получаем активные плагины
        $activePlugins = $this->pluginManager->getActivePlugins();
        error_log("Core: Active plugins count: " . count($activePlugins));

        // Для каждого активного плагина вызываем метод registerRoutes если он существует
        foreach ($activePlugins as $pluginName => $plugin) {
            error_log("Core: Processing plugin routes for: " . $pluginName);
            if (method_exists($plugin, 'registerRoutes')) {
                $plugin->registerRoutes($this->router);
                error_log("Plugin {$pluginName}: routes registered");
            } else {
                error_log("Plugin {$pluginName}: registerRoutes method not found");
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

        if (isset($this->config['debug']) && $this->config['debug']) {
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