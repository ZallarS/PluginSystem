<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Http\Pipeline;
use App\Http\Request;
use App\Http\Response;
use App\Http\Middleware\MiddlewareInterface;

class Router
{
    private array $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\StartSession::class,
            \App\Http\Middleware\HandleJsonRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\Authenticate::class,
        ],
        'api' => [
            \App\Http\Middleware\StartSession::class,
            \App\Http\Middleware\HandleJsonRequests::class,
        ],
        'guest' => [
            \App\Http\Middleware\StartSession::class,
        ]
    ];

    private $routes = [];
    private $currentRoute;
    private array $routeMiddleware = [];

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousGroupAttributes = $this->groupAttributes ?? [];

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

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function middleware(array|string $middleware): self
    {
        $this->routeMiddleware = is_array($middleware) ? $middleware : [$middleware];
        return $this;
    }

    public function addRoute($method, $uri, $action)
    {
        if (is_array($method)) {
            foreach ($method as $m) {
                $this->routes[] = new Route($m, $uri, $action);
            }
        } else {
            $this->routes[] = new Route($method, $uri, $action);
        }
    }

    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getCurrentUri();

        foreach ($this->routes as $route) {
            if ($route->matches($requestMethod, $requestUri)) {
                $this->currentRoute = $route;
                return $this->executeRoute($route);
            }
        }

        $this->show404();
    }

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
            $result->send();
        } else {
            echo $result;
        }
    }

    private function runAction($action, array $parameters)
    {
        if (is_string($action)) {
            list($controller, $method) = explode('@', $action);

            if (!class_exists($controller)) {
                throw new \Exception("Controller class not found: {$controller}");
            }

            $controllerInstance = $this->createController($controller);

            if (method_exists($controllerInstance, $method)) {
                return call_user_func_array([$controllerInstance, $method], array_values($parameters));
            } else {
                throw new \Exception("Method {$method} not found in controller {$controller}");
            }
        } elseif (is_callable($action)) {
            return call_user_func_array($action, array_values($parameters));
        }

        throw new \Exception("Invalid route action");
    }

    private function getRouteMiddleware(Route $route): array
    {
        $middleware = [];

        // Добавляем middleware из группы
        if (!empty($this->groupAttributes['middleware'])) {
            $middleware = array_merge($middleware, (array)$this->groupAttributes['middleware']);
        }

        // Добавляем middleware маршрута
        if (!empty($this->routeMiddleware)) {
            $middleware = array_merge($middleware, $this->routeMiddleware);
        }

        // Преобразуем имена middleware в классы
        return array_map(function ($name) {
            if (isset($this->middlewareGroups[$name])) {
                return $this->middlewareGroups[$name];
            }

            return $name;
        }, $middleware);
    }

    private function createController(string $controllerClass)
    {
        // Получаем Application
        $app = \App\Core\Application::getInstance();

        if (!$app) {
            return new $controllerClass();
        }

        $container = $app->getContainer();

        // Если есть фабрика в контейнере, используем ее
        if ($container && $container->has(\App\Core\ControllerFactory::class)) {
            try {
                $factory = $container->get(\App\Core\ControllerFactory::class);
                return $factory->create($controllerClass);
            } catch (\Exception $e) {
                // В случае ошибки, создаем контроллер напрямую
                if (env('APP_DEBUG', false)) {
                    error_log("Router: Error creating controller via factory: " . $e->getMessage());
                }
            }
        }

        // Fallback: создаем контроллер напрямую
        return new $controllerClass();
    }

    private function show404()
    {
        http_response_code(404);

        $errorPage = __DIR__ . '/../../../resources/views/errors/404.php';
        if (file_exists($errorPage)) {
            require $errorPage;
        } else {
            echo "404 - Page Not Found";
        }
        exit;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}