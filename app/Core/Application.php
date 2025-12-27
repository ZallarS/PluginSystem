<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Core\Providers\AppServiceProvider;
use App\Core\Routing\Router;

/**
 * Application class
 *
 * The main application class that bootstraps and runs the MVC system.
 * Manages the container, routing, plugins, and other core components.
 *
 * @package App\Core
 */
class Application
{
    /**
     * @var self|null The singleton instance
     */
    private static $instance;

    /**
     * @var Container The dependency injection container
     */
    private Container $container;

    /**
     * @var Router The routing component
     */
    private Router $router;

    /**
     * @var \Plugins\PluginManager|null The plugin manager instance
     */
    private $pluginManager;

    /**
     * Get the singleton instance of the application.
     *
     * @return self|null The application instance, or null if not initialized
     */
    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    /**
     * Create a new application instance.
     *
     * Initializes the container, bootstraps services, and sets up
     * routing and plugins.
     */
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
    }

    /**
     * Bootstrap the application services.
     *
     * Registers all application services through the service provider
     * and initializes the hook manager.
     */
    private function bootstrap(): void
    {
        // Регистрируем сервисы через провайдер
        $provider = new AppServiceProvider($this->container);
        $provider->register();

        // Инициализируем хук-менеджер
        $this->initHookManager();
    }

    /**
     * Initialize the hook manager.
     *
     * Registers all default dashboard hooks.
     */
    private function initHookManager(): void
    {
        $hookManager = HookManager::getInstance();
        $this->registerDashboardHooks($hookManager);
    }

    /**
     * Register default dashboard hooks.
     *
     * Registers all the default hooks used in the admin dashboard
     * to allow plugins and components to extend functionality.
     *
     * @param HookManager $hookManager The hook manager instance
     */
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

    /**
     * Initialize the router.
     *
     * Creates the router instance with the controller factory
     * and loads the application routes.
     */
    private function initRouter(): void
    {
        // Создаем ControllerFactory
        $controllerFactory = new ControllerFactory($this->container);
        // Создаем Router с фабрикой
        $this->router = new Router($controllerFactory);
        $this->loadRoutes();
    }

    /**
     * Load the application routes.
     *
     * Loads routes from web.php and api.php files if they exist,
     * otherwise defines fallback routes.
     */
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

    /**
     * Define fallback web routes.
     *
     * Defines default routes for the web interface when no
     * custom routes file exists.
     */
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

    /**
     * Define fallback API routes.
     *
     * Defines default API routes when no custom API routes file exists.
     */
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

    /**
     * Initialize the plugin system.
     *
     * Loads and initializes the plugin manager, system plugins,
     * and active plugins. Also initializes plugin widgets.
     */
    private function initPlugins(): void
    {
        if (class_exists('Plugins\PluginManager')) {
            try {
                // Получаем PluginManager из контейнера DI
                $this->pluginManager = $this->container->get(\Plugins\PluginManager::class);
                
                // Если не получилось через DI, используем getInstance()
                if (!$this->pluginManager) {
                    $this->pluginManager = \Plugins\PluginManager::getInstance();
                }
                
                $this->pluginManager->loadSystemPlugins();
                $this->pluginManager->loadPlugins();

                // Инициализируем виджеты плагинов
                $this->initPluginWidgets($this->pluginManager->getActivePlugins());

            } catch (\Exception $e) {
                // Log the error or handle it appropriately
            }
        }
    }

    /**
     * Initialize widgets for active plugins.
     *
     * Passes the widget manager to plugins that support it
     * and calls their init method.
     *
     * @param array $activePlugins The array of active plugins
     */
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
                    // Log the error or handle it appropriately
                }
            }
        }
    }

    /**
     * Run the application.
     *
     * Dispatches the router to handle the current request.
     * Catches any exceptions and handles them appropriately.
     */
    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle application exceptions.
     *
     * Displays detailed error information in debug mode,
     * otherwise shows a generic error page.
     *
     * @param \Exception $e The exception to handle
     */
    private function handleException(\Exception $e): void
    {
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

    /**
     * Get the application's dependency injection container.
     *
     * @return Container The DI container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the application's router.
     *
     * @return Router The router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}