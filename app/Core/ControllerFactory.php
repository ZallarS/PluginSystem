<?php

    declare(strict_types=1);

    namespace App\Core;

    use App\Core\Container\Container;
    use App\Http\Controllers\Controller;

    /**
     * ControllerFactory class
     *
     * Creates controller instances through the dependency injection container.
     * Handles controller instantiation with dependency resolution.
     *
     * @package App\Core
     */
    class ControllerFactory
    {
        /**
         * @var Container The dependency injection container
         */
        private Container $container;


        /**
         * Create a new controller factory instance.
         *
         * @param Container $container The dependency injection container
         */
        public function __construct(Container $container)
        {
            $this->container = $container;
        }

        /**
         * Create a controller instance.
         *
         * Uses the dependency injection container to instantiate
         * a controller with all its dependencies.
         *
         * @param string $controllerClass The controller class name
         * @return Controller The controller instance
         */
        public function create(string $controllerClass): Controller
        {
            // Просто создаем контроллер через контейнер
            return $this->container->make($controllerClass);
        }
    }
