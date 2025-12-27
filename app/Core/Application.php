<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Providers\AppServiceProvider;
use App\Core\Routing\Router;

class Application
{
    private static $instance;
    private Container $container;
    private Router $router;
    private $pluginManager;

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;

        // Создаем контейнер
        $this->container = new Container();

        // Загружаем хелперы (уже загружены в index.php)

        // Регистрируем сервисы
        $this->bootstrap();

        // Инициализируем компоненты
        $this->initRouter();
        $this->initPlugins();

        // Выводим только один раз
        if (self::$instance === $this) {
            error_log("Application initialized with DI container");
        }
    }

    private function bootstrap(): void
    {
        // Регистрируем сервисы через провайдер
        $provider = new AppServiceProvider($this->container);
        $provider->register();

        // Инициализируем хук-менеджер
        $this->initHookManager();
    }

    private function initHookManager(): void
    {
        $hookManager = HookManager::getInstance();
        $this->registerDashboardHooks($hookManager);
    }

    private function registerDashboardHooks(HookManager $hookManager): void
    {
        $dashboardHooks = [
            'dashboard_top', 'dashboard_before_welcome', 'dashboard_after_welcome',
            'dashboard_stats', 'dashboard_before_stats', 'dashboard_after_stats',
            'dashboard_actions', 'dashboard_before_actions', 'dashboard_after_actions',
            'dashboard_recent_activity', 'dashboard_bottom', 'dashboard_sidebar'
        ];

        foreach ($dashboardHooks as $hook) {
            $hookManager->addAction($hook, function() {});
        }
    }

    private function initRouter(): void
    {
        // Создаем ControllerFactory
        $controllerFactory = new ControllerFactory($this->container);
        // Создаем Router с фабрикой
        $this->router = new Router($controllerFactory);
        $this->loadRoutes();
    }

    private function loadRoutes(): void
    {
        $webRoutesPath = dirname(__DIR__, 2) . '/routes/web.php';
        if (file_exists($webRoutesPath)) {
            // Передаем router в контексте
            (function($router) use ($webRoutesPath) {
                require $webRoutesPath;
            })($this->router);
        } else {
            $this->defineFallbackWebRoutes();
        }

        $apiRoutesPath = dirname(__DIR__, 2) . '/routes/api.php';
        if (file_exists($apiRoutesPath)) {
            (function($router) use ($apiRoutesPath) {
                require $apiRoutesPath;
            })($this->router);
        } else {
            $this->defineFallbackApiRoutes();
        }
    }

    private function defineFallbackWebRoutes(): void
    {
        $this->router->group(['middleware' => 'web'], function ($router) {
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
        if (class_exists('Plugins\PluginManager')) {
            try {
                error_log("Application: Инициализация PluginManager");
                
                // Получаем PluginManager из контейнера DI
                $this->pluginManager = $this->container->get(\Plugins\PluginManager::class);
                
                // Если не получилось через DI, используем getInstance()
                if (!$this->pluginManager) {
                    error_log("Application: PluginManager не найден в контейнере, используем getInstance()");
                    $this->pluginManager = \Plugins\PluginManager::getInstance();
                } else {
                    error_log("Application: PluginManager получен из контейнера DI");
                }
                
                $this->pluginManager->loadSystemPlugins();
                $this->pluginManager->loadPlugins();

                // Инициализируем виджеты плагинов
                $this->initPluginWidgets($this->pluginManager->getActivePlugins());
                
                error_log("Application: PluginManager инициализирован и загрузил плагины");
            } catch (\Exception $e) {
                error_log("Core: Error initializing plugins: " . $e->getMessage());
            }
        }
    }

    private function initPluginWidgets(array $activePlugins): void
    {
        // Получаем WidgetManager из контейнера
        $widgetManager = $this->container->get(\App\Core\Widgets\WidgetManager::class);

        foreach ($activePlugins as $pluginName => $plugin) {
            if ($plugin && method_exists($plugin, 'init')) {
                try {
                    // Передаем WidgetManager в плагин, если он поддерживает это
                    if (method_exists($plugin, 'setWidgetManager')) {
                        $plugin->setWidgetManager($widgetManager);
                    }
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
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e): void
    {
        error_log("Application: Unhandled exception: " . $e->getMessage());

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

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}