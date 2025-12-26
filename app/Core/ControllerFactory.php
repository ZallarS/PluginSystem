<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Http\Controllers\Controller;
use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;
use App\Core\Session\SessionInterface;

class ControllerFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $controllerClass): Controller
    {
        // Пробуем получить зависимости из контейнера
        $template = $this->container->has(TemplateEngine::class)
            ? $this->container->get(TemplateEngine::class)
            : new TemplateEngine();

        $authService = $this->container->has(AuthService::class)
            ? $this->container->get(AuthService::class)
            : $this->getAuthServiceFallback();

        $request = $this->container->has(Request::class)
            ? $this->container->get(Request::class)
            : Request::createFromGlobals();

        $session = $this->container->has(SessionInterface::class)
            ? $this->container->get(SessionInterface::class)
            : $this->getSessionFallback();

        // Создаем контроллер с 4 параметрами
        return new $controllerClass($template, $authService, $request, $session);
    }

    private function getAuthServiceFallback(): ?AuthService
    {
        // Пытаемся получить AuthService из контейнера
        if ($this->container->has(AuthService::class)) {
            try {
                return $this->container->get(AuthService::class);
            } catch (\Exception $e) {
                error_log("ControllerFactory: Failed to get AuthService from container: " . $e->getMessage());
            }
        }

        // Fallback: создаем вручную
        try {
            $userRepository = new \App\Repositories\UserRepository();
            $sessionManager = new \App\Core\Session\SessionManager();
            return new \App\Services\AuthService($userRepository, $sessionManager);
        } catch (\Exception $e) {
            error_log("ControllerFactory: Failed to create AuthService fallback: " . $e->getMessage());
            return null;
        }
    }

    private function getSessionFallback(): SessionInterface
    {
        return new \App\Core\Session\SessionManager();
    }
}