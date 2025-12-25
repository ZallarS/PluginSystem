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

    private array $routes = [];
    private ?Route $currentRoute = null;
    private array $routeMiddleware = [];
    private array $groupAttributes = [];

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function put($uri, $action)
    {
        $this->addRoute('PUT', $uri, $action);
    }

    public function patch($uri, $action)
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    public function delete($uri, $action)
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    public function any($uri, $action)
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

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

    public function middleware(array|string $middleware): self
    {
        $this->routeMiddleware = is_array($middleware) ? $middleware : [$middleware];
        return $this;
    }

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

    private function runAction($action, array $parameters)
    {
        error_log("Trying to load controller: " . print_r($action, true));

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

    private function createController(string $controllerClass)
    {
        $app = \App\Core\Application::getInstance();

        if (!$app) {
            // Fallback с новым конструктором (4 параметра)
            return new $controllerClass(
                new \App\Core\View\TemplateEngine(),
                $this->createAuthServiceFallback(),
                \App\Http\Request::createFromGlobals(),
                new \App\Core\Session\SessionManager()
            );
        }

        $container = $app->getContainer();

        // Пытаемся создать через ControllerFactory
        if ($container && $container->has(\App\Core\ControllerFactory::class)) {
            try {
                $factory = $container->get(\App\Core\ControllerFactory::class);
                return $factory->create($controllerClass);
            } catch (\Exception $e) {
                error_log("Router: Failed to create controller via factory: " . $e->getMessage());
                // Продолжаем с fallback
            }
        }

        // Fallback: создаем вручную с новым конструктором
        try {
            return new $controllerClass(
                new \App\Core\View\TemplateEngine(),
                $this->createAuthServiceFallback(),
                \App\Http\Request::createFromGlobals(),
                new \App\Core\Session\SessionManager()
            );
        } catch (\ArgumentCountError $e) {
            // Если контроллер ожидает только 3 параметра (старая версия)
            error_log("Router: Controller expects 3 parameters, using fallback: " . $e->getMessage());

            return new $controllerClass(
                new \App\Core\View\TemplateEngine(),
                $this->createAuthServiceFallback(),
                \App\Http\Request::createFromGlobals()
            );
        }
    }

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

    private function createAuthServiceFallback()
    {
        try {
            $userRepository = new \App\Repositories\UserRepository();
            $sessionManager = new \App\Core\Session\SessionManager();
            return new \App\Services\AuthService($userRepository, $sessionManager);
        } catch (\Exception $e) {
            error_log("Router: Failed to create AuthService: " . $e->getMessage());
            return null;
        }
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->currentRoute;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }
}