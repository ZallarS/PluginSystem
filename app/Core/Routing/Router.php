<?php
declare(strict_types=1);

namespace App\Core\Routing;

class Router
{
    private $routes = [];
    private $currentRoute;

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
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

        if (is_string($action)) {
            list($controller, $method) = explode('@', $action);

            if (!class_exists($controller)) {
                throw new \Exception("Controller class not found: {$controller}");
            }

            // Получаем Application и создаем контроллер через фабрику
            $app = \App\Core\Application::getInstance();
            if ($app && method_exists($app, 'getContainer')) {
                $container = $app->getContainer();

                // Если есть фабрика в контейнере, используем ее
                if ($container && $container->has(\App\Core\ControllerFactory::class)) {
                    $factory = $container->get(\App\Core\ControllerFactory::class);
                    $controllerInstance = $factory->create($controller);
                } else {
                    // Fallback: создаем напрямую
                    $controllerInstance = new $controller();
                }
            } else {
                // Fallback: создаем напрямую
                $controllerInstance = new $controller();
            }

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