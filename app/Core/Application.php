<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Routing\Router;

class Application
{
    private static $instance;
    private $router;
    private $config = [];
    private $pluginManager;
    private $hookManager;

    public static function getInstance()
    {
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;

        // Загружаем хелперы
        require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';

        $this->initHookManager();
        $this->initRouter();
        $this->initPlugins();
    }

    private function initHookManager()
    {
        $this->hookManager = HookManager::getInstance();
        $this->registerDashboardHooks();
    }

    private function registerDashboardHooks()
    {
        $dashboardHooks = [
            'dashboard_top', 'dashboard_before_welcome', 'dashboard_after_welcome',
            'dashboard_stats', 'dashboard_before_stats', 'dashboard_after_stats',
            'dashboard_actions', 'dashboard_before_actions', 'dashboard_after_actions',
            'dashboard_recent_activity', 'dashboard_bottom', 'dashboard_sidebar'
        ];

        foreach ($dashboardHooks as $hook) {
            $this->hookManager->addAction($hook, function() {});
        }
    }

    private function initRouter()
    {
        $this->router = new Router();

        // Базовые маршруты
        $this->router->get('/', 'App\Http\Controllers\HomeController@index');
        $this->router->get('/admin', 'App\Http\Controllers\AdminController@dashboard');

        // Аутентификация
        $this->router->get('/login', 'App\Http\Controllers\AuthController@login');
        $this->router->post('/login', 'App\Http\Controllers\AuthController@login');
        $this->router->get('/logout', 'App\Http\Controllers\AuthController@logout');
        $this->router->get('/quick-login', 'App\Http\Controllers\AuthController@quickLogin');

        // Управление плагинами
        $this->router->get('/admin/plugins', 'App\Http\Controllers\PluginController@index');
        $this->router->post('/admin/plugins/activate/{pluginName}', 'App\Http\Controllers\PluginController@activate');
        $this->router->post('/admin/plugins/deactivate/{pluginName}', 'App\Http\Controllers\PluginController@deactivate');

        // Управление виджетами
        $this->router->post('/admin/save-widgets', 'App\Http\Controllers\AdminController@saveWidgets');
        $this->router->post('/admin/toggle-widget', 'App\Http\Controllers\AdminController@toggleWidget');
        $this->router->get('/admin/get-hidden-widgets', 'App\Http\Controllers\AdminController@getHiddenWidgets');
        $this->router->get('/admin/widget-info/{widgetId}', 'App\Http\Controllers\AdminController@getWidgetInfo');
        $this->router->get('/admin/widget-html/{widgetId}', 'App\Http\Controllers\AdminController@getWidgetHtml');

        // Дополнительные маршруты
        $this->router->get('/about', function() {
            echo "Страница о системе (в разработке)";
        });

        $this->router->get('/docs', function() {
            echo "Документация (в разработке)";
        });
    }

    private function initPlugins()
    {
        error_log("Core: Initializing plugins...");

        if (class_exists('Plugins\PluginManager')) {
            $this->pluginManager = \Plugins\PluginManager::getInstance();
            $pluginsRegistered = $this->pluginManager->loadPlugins();
            error_log("Core: Plugins registered: " . $pluginsRegistered);

            $activePlugins = $this->pluginManager->getActivePlugins();
            error_log("Core: Active plugins count: " . count($activePlugins));

            $this->initPluginWidgets($activePlugins);

            foreach ($activePlugins as $pluginName => $plugin) {
                if ($plugin && method_exists($plugin, 'registerRoutes')) {
                    $plugin->registerRoutes($this->router);
                }
            }
        } else {
            error_log("Core: PluginManager class not found, plugins disabled");
        }
    }

    private function initPluginWidgets($activePlugins)
    {
        error_log("Core: Initializing plugin widgets...");

        foreach ($activePlugins as $pluginName => $plugin) {
            if ($plugin && method_exists($plugin, 'init')) {
                error_log("Core: Initializing plugin: " . $pluginName);
                $plugin->init();
            }
        }
    }

    public function run()
    {
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