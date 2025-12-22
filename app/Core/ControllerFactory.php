<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Http\Controllers\Controller;
use App\Core\View\TemplateEngine;
use App\Services\AuthService;
use App\Http\Request;

class ControllerFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $controllerClass): Controller
    {
        // Получаем зависимости из контейнера
        $template = $this->container->has(TemplateEngine::class)
            ? $this->container->get(TemplateEngine::class)
            : new TemplateEngine();

        $authService = $this->container->has(AuthService::class)
            ? $this->container->get(AuthService::class)
            : null;

        $request = $this->container->has(Request::class)
            ? $this->container->get(Request::class)
            : Request::createFromGlobals();

        return new $controllerClass($template, $authService, $request);
    }
}