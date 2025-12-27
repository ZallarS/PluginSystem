<?php

    namespace App\Core\Routing;

    use App\Core\ControllerFactory;
    use App\Http\Pipeline;
    use App\Http\Request;
    use App\Http\Response;
    use App\Http\Middleware\MiddlewareInterface;

    /**
     * Router class
     *
     * Handles routing of HTTP requests to appropriate controllers and actions.
     * Supports route groups, middleware, and flexible route definitions.
     *
     * @package App\Core\Routing
     */
    class Router
    {
        /**
         * @var array<string, array<string>> The middleware groups
         */
        private array $middlewareGroups = [
            'web' => [
                \App\Http\Middleware\StartSession::class,
                \App\Http\Middleware\HandleJsonRequests::class,
                \App\Http\Middleware\Authenticate::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
            ],
            'api' => [
                \App\Http\Middleware\StartSession::class,
                \App\Http\Middleware\HandleJsonRequests::class,
            ],
            'guest' => [
                \App\Http\Middleware\StartSession::class,
            ]
        ];

        /**
         * @var array<Route> The registered routes
         */
        private array $routes = [];

        /**
         * @var Route|null The currently matched route
         */
        private ?Route $currentRoute = null;

        /**
         * @var array The middleware for the current route
         */
        private array $routeMiddleware = [];

        /**
         * @var array The attributes for the current route group
         */
        private array $groupAttributes = [];

        /**
         * @var ControllerFactory The controller factory instance
         */
        private ControllerFactory $controllerFactory;

        /**
         * Create a new router instance.
         *
         * @param ControllerFactory $controllerFactory The controller factory
         */
        public function __construct(ControllerFactory $controllerFactory)
        {
            $this->controllerFactory = $controllerFactory;
        }

        /**
         * Register a GET route.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function get($uri, $action)
        {
            $this->addRoute('GET', $uri, $action);
        }

        /**
         * Register a POST route.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function post($uri, $action)
        {
            $this->addRoute('POST', $uri, $action);
        }

        /**
         * Register a PUT route.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function put($uri, $action)
        {
            $this->addRoute('PUT', $uri, $action);
        }

        /**
         * Register a PATCH route.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function patch($uri, $action)
        {
            $this->addRoute('PATCH', $uri, $action);
        }

        /**
         * Register a DELETE route.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function delete($uri, $action)
        {
            $this->addRoute('DELETE', $uri, $action);
        }

        /**
         * Register a route for any HTTP method.
         *
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        public function any($uri, $action)
        {
            $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
        }

        /**
         * Define a route group with shared attributes.
         *
         * @param array $attributes The group attributes (prefix, middleware, etc.)
         * @param callable $callback The callback to register routes
         * @return void
         */
        public function group(array $attributes, callable $callback): void
        {
            $previousGroupAttributes = $this->groupAttributes;

            // Объединяем атрибуты группы
            $this->groupAttributes = array_merge($previousGroupAttributes, [
                'prefix' => $attributes['prefix'] ?? '',
                'middleware' => $attributes['middleware'] ?? [],
            ]);

            // Вызываем callback для регистрации маршрутов группы
            $callback($this);

            // Восстанавливаем предыдущие атрибуты
            $this->groupAttributes = $previousGroupAttributes;
        }

        /**
         * Set middleware for the next route registration.
         *
         * @param array|string $middleware The middleware to apply
         * @return self The router instance for method chaining
         */
        public function middleware(array|string $middleware): self
        {
            $this->routeMiddleware = is_array($middleware) ? $middleware : [$middleware];
            return $this;
        }

        /**
         * Add a route to the router.
         *
         * @param string|array $method The HTTP method(s) for the route
         * @param string $uri The URI pattern
         * @param mixed $action The action to execute
         * @return void
         */
        private function addRoute($method, $uri, $action)
        {
            // Применяем префикс группы
            if (!empty($this->groupAttributes['prefix'])) {
                $uri = rtrim($this->groupAttributes['prefix'], '/') . '/' . ltrim($uri, '/');
            }

            if (is_array($method)) {
                foreach ($method as $m) {
                    $this->routes[] = new Route($m, $uri, $action);
                }
            } else {
                $this->routes[] = new Route($method, $uri, $action);
            }
        }

        /**
         * Dispatch the current request to the appropriate route.
         *
         * Matches the current request against registered routes and
         * executes the corresponding action through the middleware pipeline.
         *
         * @return void
         */
        public function dispatch()
        {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            $requestUri = $this->getCurrentUri();

            // Обрабатываем HEAD запросы как GET
            $searchMethod = ($requestMethod === 'HEAD') ? 'GET' : $requestMethod;

            foreach ($this->routes as $route) {
                if ($route->matches($searchMethod, $requestUri)) {
                    $this->currentRoute = $route;
                    return $this->executeRoute($route);
                }
            }

            $this->show404();
        }

        /**
         * Get the current URI from the request.
         *
         * Removes the script name from the URI if it's not in the document root.
         *
         * @return string The current URI
         */
        private function getCurrentUri()
        {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $uri = rawurldecode($uri);

            $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

            if ($basePath !== '/') {
                $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
            }

            return $uri ?: '/';
        }

        /**
         * Execute the given route through the middleware pipeline.
         *
         * @param Route $route The route to execute
         * @return void
         */
        private function executeRoute(Route $route)
        {
            $action = $route->getAction();
            $parameters = $route->getParameters();

            // Получаем middleware для маршрута
            $middleware = $this->getRouteMiddleware($route);

            // Создаем pipeline
            $pipeline = new Pipeline();

            $result = $pipeline
                ->send(Request::createFromGlobals())
                ->through($middleware)
                ->then(function ($request) use ($action, $parameters) {
                    return $this->runAction($action, $parameters);
                });

            // Отправляем результат
            if ($result instanceof Response) {
                if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
                    // Вместо $result->sendHeaders() просто отправим заголовки
                    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                    // Здесь можно добавить другие заголовки из $result
                    return; // Выходим без вывода тела
                } else {
                    $result->send();
                }
            } else {
                // Если результат не объект Response
                if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
                    echo $result;
                }
            }
        }

        /**
         * Run the action for the given route.
         *
         * Supports various action formats: [Controller::class, 'method'],
         * 'Controller@method', and callable functions.
         *
         * @param mixed $action The action to execute
         * @param array $parameters The route parameters
         * @return mixed The result of the action
         * @throws \Exception If the action cannot be executed
         */
        private function runAction($action, array $parameters)
        {
            $controller = null;
            $method = null;

            // Обработка массива [Controller::class, 'method']
            if (is_array($action)) {
                $controller = $action[0];
                $method = $action[1];
            }
            // Обработка строки 'Controller@method'
            elseif (is_string($action)) {
                if (strpos($action, '@') !== false) {
                    list($controller, $method) = explode('@', $action);
                } else {
                    // Если только контроллер без метода
                    $controller = $action;
                    $method = 'index';
                }
            }
            // Обработка callable
            elseif (is_callable($action)) {
                return call_user_func_array($action, array_values($parameters));
            }

            // Если не удалось определить контроллер и метод
            if (!$controller || !$method) {
                throw new \Exception("Invalid route action: " . print_r($action, true));
            }

            if (!class_exists($controller)) {
                throw new \Exception("Controller class not found: {$controller}");
            }

            $controllerInstance = $this->createController($controller);

            if (method_exists($controllerInstance, $method)) {
                return call_user_func_array([$controllerInstance, $method], array_values($parameters));
            } else {
                throw new \Exception("Method {$method} not found in controller {$controller}");
            }
        }

        /**
         * Get the middleware for the given route.
         *
         * Combines middleware from the route group and resolves
         * middleware group names to actual middleware classes.
         *
         * @param Route $route The route
         * @return array The middleware array
         */
        private function getRouteMiddleware(Route $route): array
        {
            $middleware = [];

            // Добавляем middleware из группы
            if (!empty($this->groupAttributes['middleware'])) {
                $groupMiddleware = (array)$this->groupAttributes['middleware'];
                foreach ($groupMiddleware as $mw) {
                    if (isset($this->middlewareGroups[$mw])) {
                        $middleware = array_merge($middleware, $this->middlewareGroups[$mw]);
                    } else {
                        $middleware[] = $mw;
                    }
                }
            }

            // Преобразуем имена middleware в классы
            return array_map(function ($name) {
                if (isset($this->middlewareGroups[$name])) {
                    return $this->middlewareGroups[$name];
                }
                return $name;
            }, $middleware);
        }

        /**
         * Create a controller instance.
         *
         * @param string $controllerClass The controller class name
         * @return object The controller instance
         */
        private function createController(string $controllerClass)
        {
            return $this->controllerFactory->create($controllerClass);
        }

        /**
         * Show a 404 error page.
         *
         * Displays the custom 404 page if it exists, otherwise
         * shows a simple error message.
         *
         * @return void
         */
        private function show404()
        {
            http_response_code(404);

            $errorPage = dirname(__DIR__, 3) . '/resources/views/errors/404.php';
            if (file_exists($errorPage)) {
                require $errorPage;
            } else {
                echo "404 - Page Not Found";
            }
            exit;
        }

        /**
         * Get the currently matched route.
         *
         * @return Route|null The current route, or null if no route matched
         */
        public function getCurrentRoute(): ?Route
        {
            return $this->currentRoute;
        }

        /**
         * Get all registered routes.
         *
         * @return array The routes
         */
        public function getRoutes(): array
        {
            return $this->routes;
        }

        /**
         * Get the middleware groups.
         *
         * @return array The middleware groups
         */
        public function getMiddlewareGroups(): array
        {
            return $this->middlewareGroups;
        }
    }