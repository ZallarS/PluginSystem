<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Http\Controllers\Controller;
use App\Core\View\TemplateEngine;
use App\Services\AuthService;

class ControllerFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $controllerClass): Controller
    {
        // Получаем TemplateEngine из контейнера или создаем новый
        $template = $this->container->has(TemplateEngine::class)
            ? $this->container->get(TemplateEngine::class)
            : new TemplateEngine();

        // Получаем AuthService из контейнера (может быть null)
        $authService = $this->container->has(AuthService::class)
            ? $this->container->get(AuthService::class)
            : null;

        return new $controllerClass($template, $authService);
    }
}