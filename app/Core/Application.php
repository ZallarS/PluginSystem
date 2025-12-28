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

        // Проверяем основные шаблоны
        $this->checkEssentialTemplates();

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
        // Устанавливаем обработчики ошибок и исключений ДО запуска приложения
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);

        try {
            $this->router->dispatch();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * Handle PHP errors.
     *
     * @param int $level The error level
     * @param string $message The error message
     * @param string $file The file where the error occurred
     * @param int $line The line number where the error occurred
     * @return bool True to prevent PHP's internal error handler from running
     */
    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }

        return true;
    }

    /**
     * Handle application exceptions.
     *
     * Displays detailed error information in debug mode,
     * otherwise shows a generic error page.
     *
     * @param \Exception $e The exception to handle
     */
    private function handleException(\Throwable $e): void
    {
        // Логируем ошибку
        try {
            if (class_exists(\App\Core\Logger::class)) {
                $logger = \App\Core\Logger::getInstance();
                $logger->error($e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } catch (\Throwable $logException) {
            // Игнорируем ошибки логгирования
        }

        // Отображаем красивую страницу ошибки
        $this->showErrorPage($e);
    }

    /**
     * Display a beautiful error page.
     *
     * @param \Throwable $e The exception
     */
    private function showErrorPage(\Throwable $e): void
    {
        // Устанавливаем заголовки только если они еще не отправлены
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        // Пытаемся показать красивую страницу 500
        $errorPage = dirname(__DIR__, 2) . '/resources/views/errors/500.php';
        if (file_exists($errorPage)) {
            // Передаем информацию об ошибке в шаблон
            $debug = env('APP_DEBUG', false);
            $title = '500 - Internal Server Error';
            $message = 'Произошла внутренняя ошибка сервера.';

            // Буферизируем вывод, чтобы перехватить любые ошибки при рендеринге
            ob_start();
            try {
                include $errorPage;
                $content = ob_get_clean();
                echo $content;
            } catch (\Throwable $templateError) {
                ob_end_clean();
                // Если даже шаблон ошибки не смог загрузиться, покажем простую страницу
                $this->showFallbackErrorPage($e);
            }
        } else {
            $this->showFallbackErrorPage($e);
        }

        exit(1);
    }

    /**
     * Display a fallback error page when the main error template fails.
     *
     * @param \Throwable $e The exception
     */
    private function showFallbackErrorPage(\Throwable $e): void
    {
        $debug = env('APP_DEBUG', false);

        echo '<!DOCTYPE html>';
        echo '<html lang="ru">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>500 - Internal Server Error</title>';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 50px; text-align: center; }';
        echo '.error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
        echo 'h1 { color: #dc3545; }';
        echo 'pre { background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: left; overflow: auto; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="error-container">';
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Произошла внутренняя ошибка сервера.</p>';

        if ($debug) {
            echo '<div style="margin-top: 30px; text-align: left;">';
            echo '<h3>Debug Information:</h3>';
            echo '<p><strong>' . htmlspecialchars($e->getMessage()) . '</strong></p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        } else {
            echo '<p>Пожалуйста, попробуйте позже или свяжитесь с администратором.</p>';
            echo '<a href="/">Вернуться на главную</a>';
        }

        echo '</div>';
        echo '</body>';
        echo '</html>';
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

    /**
     * Check if essential templates exist.
     *
     * @return bool True if all essential templates exist
     */
    private function checkEssentialTemplates(): bool
    {
        $essentialTemplates = [
            'home.index',
            'auth.login',
            'admin.dashboard',
            'admin.plugins',
            'admin.hidden-widgets',
            'errors.404',
            'errors.500'
        ];

        $missingTemplates = [];

        foreach ($essentialTemplates as $template) {
            $path = $this->resolveTemplatePath($template);
            if (!file_exists($path)) {
                $missingTemplates[] = $template;
                error_log("Warning: Template not found: {$template} (expected at: {$path})");
            }
        }

        if (!empty($missingTemplates)) {
            error_log("Missing templates: " . implode(', ', $missingTemplates));
            // Создаем простые шаблоны на лету для недостающих
            $this->createMissingTemplates($missingTemplates);
        }

        return true;
    }

    private function createMissingTemplates(array $templates): void
    {
        $templateDir = dirname(__DIR__, 2) . '/resources/views/';

        foreach ($templates as $template) {
            $parts = explode('.', $template);
            $dir = $templateDir . $parts[0];
            $file = $dir . '/' . $parts[1] . '.php';

            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            // Создаем простой шаблон
            $content = "<?php\n// Auto-generated template for: {$template}\n?>\n";
            $content .= "<!DOCTYPE html>\n<html>\n<head><title>{$parts[1]}</title></head>\n";
            $content .= "<body>\n<h1>Template: {$template}</h1>\n<p>This template was auto-generated.</p>\n</body>\n</html>";

            file_put_contents($file, $content);
            error_log("Created missing template: {$file}");
        }
    }

    /**
     * Resolve template path for checking.
     *
     * @param string $template The template name
     * @return string The full path
     */
    private function resolveTemplatePath(string $template): string
    {
        $template = str_replace('.', '/', $template);
        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        return dirname(__DIR__, 2) . '/resources/views/' . $template;
    }
}