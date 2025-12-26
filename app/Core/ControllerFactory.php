<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container\Container;
use App\Http\Controllers\Controller;

class ControllerFactory
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function create(string $controllerClass): Controller
    {
        // Просто создаем контроллер через контейнер с автовайрингом
        return $this->container->make($controllerClass);
    }
}