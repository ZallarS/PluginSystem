<?php
declare(strict_types=1);

namespace App\Core\Providers;

use App\Core\Container\Container;
use App\Core\Session\SessionManager;
use App\Core\View\TemplateEngine;
use App\Core\Widgets\WidgetManager;
use App\Services\AuthService;
use App\Repositories\UserRepository;

class AppServiceProvider
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        // Регистрируем сам контейнер
        $this->container->instance(Container::class, $this->container);
        $this->container->alias(Container::class, 'container');

        // Регистрируем синглтоны
        $this->registerSingletons();

        // Регистрируем зависимости
        $this->registerDependencies();
    }

    private function registerSingletons(): void
    {
        // SessionManager как синглтон
        $this->container->singleton(SessionManager::class, function() {
            return new SessionManager();
        });
        $this->container->singleton(SessionInterface::class, SessionManager::class);

        // TemplateEngine как синглтон
        $this->container->singleton(TemplateEngine::class, function() {
            return new TemplateEngine();
        });

        // HookManager как синглтон
        $this->container->singleton(\App\Core\HookManager::class, function() {
            return \App\Core\HookManager::getInstance();
        });

        // WidgetManager как синглтон
        $this->container->singleton(WidgetManager::class, function($container) {
            $session = $container->get(SessionManager::class);
            return WidgetManager::getInstance($session);
        });
    }

    private function registerDependencies(): void
    {
        // UserRepository с опциональной зависимостью от PDO
        $this->container->singleton(UserRepository::class, function() {
            $pdo = $this->getPdoConnection();
            return new UserRepository($pdo);
        });

        // AuthService зависит от UserRepository и SessionManager
        $this->container->singleton(AuthService::class, function($container) {
            $userRepository = $container->get(UserRepository::class);
            $session = $container->get(SessionManager::class);
            return new AuthService($userRepository, $session);
        });

        // Request как синглтон
        $this->container->singleton(\App\Http\Request::class, function() {
            return \App\Http\Request::createFromGlobals();
        });

        // ControllerFactory
        $this->container->singleton(\App\Core\ControllerFactory::class, function($container) {
            return new \App\Core\ControllerFactory($container);
        });
    }

    private function getPdoConnection(): ?\PDO
    {
        if (!env('DB_HOST') || !env('DB_DATABASE')) {
            return null;
        }

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
            error_log("Failed to create PDO connection: " . $e->getMessage());
            return null;
        }
    }
}