<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Routing\Router;

class Application
{
    private static $instance;
    private Container $container;
    private Router $router;
    private $pluginManager;
    private HookManager $hookManager;

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;

        // Создаем простой контейнер
        $this->container = new Container();

        // Регистрируем только самое необходимое
        $this->registerEssentialBindings();

        // Загружаем хелперы
        require_once dirname(__DIR__, 2) . '/bootstrap/helpers.php';

        $this->initHookManager();
        $this->initRouter();
        $this->initPlugins();
    }

    private function registerEssentialBindings(): void
    {
        try {
            // Регистрируем сам контейнер
            $this->container->instance(Container::class, $this->container);
            $this->container->alias(Container::class, 'container');

            // Регистрируем хук-менеджер
            $this->container->instance(HookManager::class, HookManager::getInstance());

            // Регистрируем WidgetManager
            $this->container->singleton(\App\Core\Widgets\WidgetManager::class, function() {
                return \App\Core\Widgets\WidgetManager::getInstance();
            });

            // Регистрируем PDO соединение
            $this->container->singleton(\PDO::class, function() {
                try {
                    $dsn = sprintf(
                        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                        env('DB_HOST', 'localhost'),
                        env('DB_PORT', '3306'),
                        env('DB_DATABASE', 'SystemPlugins')
                    );

                    $pdo = new \PDO(
                        $dsn,
                        env('DB_USERNAME', 'root'),
                        env('DB_PASSWORD', ''),
                        [
                            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                            \PDO::ATTR_EMULATE_PREPARES => false
                        ]
                    );

                    // Проверяем соединение
                    $pdo->query('SELECT 1');
                    error_log("Application: Database connection successful");
                    return $pdo;

                } catch (\PDOException $e) {
                    error_log("Application: Database connection failed: " . $e->getMessage());
                    return null;
                } catch (\Exception $e) {
                    error_log("Application: Error creating PDO: " . $e->getMessage());
                    return null;
                }
            });

            // Регистрируем UserRepository
            $this->container->singleton(\App\Repositories\UserRepository::class, function($container) {
                $pdo = $container->get(\PDO::class);
                return new \App\Repositories\UserRepository($pdo);
            });

            // Регистрируем AuthService
            $this->container->singleton(\App\Services\AuthService::class, function($container) {
                $userRepository = $container->get(\App\Repositories\UserRepository::class);
                return new \App\Services\AuthService($userRepository);
            });

            error_log("Application: Essential bindings registered");

        } catch (\Exception $e) {
            error_log("Application: Error registering bindings: " . $e->getMessage());
        }
    }


    private function initHookManager(): void
    {
        $this->hookManager = HookManager::getInstance();
        $this->registerDashboardHooks();
    }

    private function registerDashboardHooks(): void
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

    private function initRouter(): void
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

        // Тестовый маршрут
        $this->router->get('/test', 'App\Http\Controllers\TestController@index');
    }

    private function initPlugins(): void
    {
        error_log("Core: Initializing plugins...");

        if (class_exists('Plugins\PluginManager')) {
            try {
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
            } catch (\Exception $e) {
                error_log("Core: Error initializing plugins: " . $e->getMessage());
            }
        } else {
            error_log("Core: PluginManager class not found, plugins disabled");
        }
    }

    private function initPluginWidgets($activePlugins): void
    {
        error_log("Core: Initializing plugin widgets...");

        foreach ($activePlugins as $pluginName => $plugin) {
            if ($plugin && method_exists($plugin, 'init')) {
                error_log("Core: Initializing plugin: " . $pluginName);
                try {
                    $plugin->init();
                } catch (\Exception $e) {
                    error_log("Core: Error initializing plugin {$pluginName}: " . $e->getMessage());
                }
            }
        }
    }

    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            error_log("Application: Unhandled exception: " . $e->getMessage());
            error_log($e->getTraceAsString());

            http_response_code(500);

            if (env('APP_DEBUG', false)) {
                echo "<h1>Application Error</h1>";
                echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
                echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            } else {
                $errorPage = dirname(__DIR__, 2) . '/resources/views/errors/500.php';
                if (file_exists($errorPage)) {
                    include $errorPage;
                } else {
                    echo "<h1>500 - Internal Server Error</h1>";
                    echo "<p>Произошла внутренняя ошибка сервера.</p>";
                }
            }
        }
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getPluginManager()
    {
        return $this->pluginManager;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}