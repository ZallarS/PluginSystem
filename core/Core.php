<?php

namespace Core;

use Core\Router\Router;

class Core
{
    private static $instance;
    private $router;
    private $config = [];
    private $pluginManager;
    private $hookManager;

    public function __construct()
    {
        self::$instance = $this;
        $this->loadConfig();
        $this->initHookManager(); // <-- Добавьте эту строку
        $this->initRouter();
        $this->initPlugins();
    }

    private function registerDashboardHooks()
    {
        // Основные хуки для дашборда
        $dashboardHooks = [
            'dashboard_top',
            'dashboard_before_welcome',
            'dashboard_after_welcome',
            'dashboard_stats',
            'dashboard_before_stats',
            'dashboard_after_stats',
            'dashboard_actions',
            'dashboard_before_actions',
            'dashboard_after_actions',
            'dashboard_recent_activity',
            'dashboard_bottom',
            'dashboard_sidebar'
        ];

        foreach ($dashboardHooks as $hook) {
            $this->hookManager->addAction($hook, function() {
                // Пустой колбек по умолчанию
            });
        }
    }

    public function getHookManager()
    {
        return $this->hookManager;
    }

    private function initHookManager()
    {
        $this->hookManager = \Core\HookManager::getInstance();

        // Регистрируем базовые хуки дашборда
        $this->registerDashboardHooks();
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
        $this->router = new Router();

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

        error_log("Base routes registered");
    }

    private function initPlugins()
    {
        // Загружаем конфигурацию плагинов
        $pluginsConfigPath = __DIR__ . '/../app/Config/plugins.php';
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

        // Загружаем плагины (активные загрузятся автоматически)
        $pluginsRegistered = $this->pluginManager->loadPlugins();
        error_log("Core: Plugins registered: " . $pluginsRegistered);

        // Получаем все плагины
        $plugins = $this->pluginManager->getPlugins();
        error_log("Core: Total plugins: " . count($plugins));

        // Получаем активные плагины
        $activePlugins = $this->pluginManager->getActivePlugins();
        error_log("Core: Active plugins count: " . count($activePlugins));

        // Для каждого активного плагина вызываем метод registerRoutes если он существует
        foreach ($activePlugins as $pluginName => $plugin) {
            error_log("Core: Processing plugin routes for: " . $pluginName);
            if ($plugin && method_exists($plugin, 'registerRoutes')) {
                $plugin->registerRoutes($this->router);
                error_log("Plugin {$pluginName}: routes registered");
            } else {
                error_log("Plugin {$pluginName}: registerRoutes method not found or plugin not loaded");
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

    public function getRouter()
    {
        return $this->router;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }
}