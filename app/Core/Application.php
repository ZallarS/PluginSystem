<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Routing\Router;
use App\Core\Logger;

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

            // Упрощенная регистрация PDO - только если есть настройки БД
            if (env('DB_HOST') && env('DB_DATABASE')) {
                $this->container->singleton(\PDO::class, function() {
                    try {
                        $dsn = sprintf(
                            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                            env('DB_HOST', 'localhost'),
                            env('DB_PORT', '3306'),
                            env('DB_DATABASE')
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
                        return $pdo;

                    } catch (\PDOException $e) {
                        // Возвращаем null вместо выбрасывания исключения
                        return null;
                    }
                });
            } else {
                // Если нет настроек БД, регистрируем null
                $this->container->instance(\PDO::class, null);
            }

            // Регистрируем UserRepository с проверкой PDO
            $this->container->singleton(\App\Repositories\UserRepository::class, function($container) {
                $pdo = $container->get(\PDO::class);
                return new \App\Repositories\UserRepository($pdo);
            });

            // Регистрируем AuthService
            $this->container->singleton(\App\Services\AuthService::class, function($container) {
                $userRepository = $container->get(\App\Repositories\UserRepository::class);
                return new \App\Services\AuthService($userRepository);
            });

            // Регистрируем TemplateEngine
            $this->container->singleton(\App\Core\View\TemplateEngine::class, function() {
                return new \App\Core\View\TemplateEngine();
            });

            // Регистрируем ControllerFactory
            $this->container->singleton(\App\Core\ControllerFactory::class, function($container) {
                return new \App\Core\ControllerFactory($container);
            });

            // Регистрируем WidgetService
            $this->container->singleton(\App\Services\WidgetService::class, function($container) {
                $widgetManager = $container->has(\App\Core\Widgets\WidgetManager::class)
                    ? $container->get(\App\Core\Widgets\WidgetManager::class)
                    : \App\Core\Widgets\WidgetManager::getInstance();

                $hookManager = $container->has(\App\Core\HookManager::class)
                    ? $container->get(\App\Core\HookManager::class)
                    : \App\Core\HookManager::getInstance();

                return new \App\Services\WidgetService($widgetManager, $hookManager);
            });

            $this->container->singleton(\App\Repositories\WidgetRepository::class, function($container) {
                $widgetManager = $container->get(\App\Core\Widgets\WidgetManager::class);
                return new \App\Repositories\WidgetRepository($widgetManager);
            });

            // Регистрируем CacheService
            $this->container->singleton(\App\Services\CacheService::class, function() {
                $cachePath = storage_path('cache');
                $enabled = config('widget_cache.enabled', true);

                if (!$enabled) {
                    return null; // Возвращаем null если кэширование отключено
                }

                $ttl = config('widget_cache.ttl', 300);
                return new \App\Services\CacheService($cachePath, $ttl);
            });

            // Регистрируем Middleware
            $this->container->singleton(\App\Http\Middleware\StartSession::class, function() {
                return new \App\Http\Middleware\StartSession();
            });

            $this->container->singleton(\App\Http\Middleware\Authenticate::class, function($container) {
                return new \App\Http\Middleware\Authenticate();
            });

            $this->container->singleton(\App\Http\Middleware\VerifyCsrfToken::class, function() {
                return new \App\Http\Middleware\VerifyCsrfToken();
            });

            $this->container->singleton(\App\Http\Middleware\HandleJsonRequests::class, function() {
                return new \App\Http\Middleware\HandleJsonRequests();
            });


        } catch (\Exception $e) {
            // Логируем только в режиме отладки
            if (env('APP_DEBUG', false)) {
                Logger::getInstance()->error("Application: Error in bindings", ['exception' => $e->getMessage()]);
            }
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
        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        $router = $this->router;

        // Загружаем web маршруты
        $webRoutesPath = dirname(__DIR__, 2) . '/routes/web.php';
        if (file_exists($webRoutesPath)) {
            require $webRoutesPath;
        } else {
            $this->defineFallbackWebRoutes();
        }

        // Загружаем API маршруты
        $apiRoutesPath = dirname(__DIR__, 2) . '/routes/api.php';
        if (file_exists($apiRoutesPath)) {
            require $apiRoutesPath;
        } else {
            $this->defineFallbackApiRoutes();
        }
    }

    private function defineFallbackWebRoutes(): void
    {
        $this->router->group(['middleware' => 'web'], function ($router) {
            // Минимальный набор маршрутов для работы системы
            $router->get('/', 'App\Http\Controllers\HomeController@index');
            $router->get('/login', 'App\Http\Controllers\AuthController@login');
            $router->post('/login', 'App\Http\Controllers\AuthController@login');
            $router->get('/logout', 'App\Http\Controllers\AuthController@logout');
            $router->get('/admin', 'App\Http\Controllers\AdminController@dashboard');
        });
    }

    private function defineFallbackApiRoutes(): void
    {
        $this->router->group([
            'prefix' => '/api',
            'middleware' => 'api'
        ], function ($router) {
            $router->get('/status', function() {
                return \App\Http\Response::json(['status' => 'ok', 'timestamp' => time()]);
            });
        });
    }

    private function initPlugins(): void
    {
        error_log("Core: Initializing plugins...");

        if (class_exists('Plugins\PluginManager')) {
            try {
                $this->pluginManager = \Plugins\PluginManager::getInstance();

                // Загружаем системные плагины
                $this->pluginManager->loadSystemPlugins();

                // Затем загружаем обычные плагины
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