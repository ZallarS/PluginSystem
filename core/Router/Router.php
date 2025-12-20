<?php

namespace Core\Router;

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

    private function addRoute($method, $uri, $action)
    {
        $this->routes[] = new Route($method, $uri, $action);
        error_log("Route added: {$method} {$uri} -> " . (is_string($action) ? $action : 'callback'));
    }

    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getCurrentUri();

        error_log("=== DEBUG DISPATCH START ===");
        error_log("Request Method: {$requestMethod}");
        error_log("Request URI: {$requestUri}");

        // Логируем все доступные маршруты
        error_log("Available routes:");
        foreach ($this->routes as $route) {
            error_log("  {$route->getMethod()} {$route->getUri()} -> " .
                (is_string($route->getAction()) ? $route->getAction() : 'callback'));
        }

        foreach ($this->routes as $route) {
            error_log("Checking route: {$route->getMethod()} {$route->getUri()}");
            if ($route->matches($requestMethod, $requestUri)) {
                $this->currentRoute = $route;
                error_log("Route matched: " . $route->getUri());
                return $this->executeRoute($route);
            }
        }

        error_log("No route found for: {$requestMethod} {$requestUri}");
        $this->show404();
    }

    private function getCurrentUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rawurldecode($uri);

        error_log("DEBUG: Original URI: " . $uri);
        error_log("DEBUG: SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);

        // Убираем базовый путь, если есть
        $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        error_log("DEBUG: Base path: " . $basePath);

        if ($basePath !== '/') {
            $uri = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $uri);
        }

        error_log("DEBUG: Final URI: " . $uri);
        return $uri ?: '/';
    }

    private function executeRoute(Route $route)
    {
        $action = $route->getAction();
        $parameters = $route->getParameters();

        error_log("Executing route: " . print_r($action, true));

        if (is_string($action)) {
            list($controller, $method) = explode('@', $action);

            // Проверяем существование класса
            if (!class_exists($controller)) {
                error_log("Controller class not found: {$controller}");
                throw new \Exception("Controller class not found: {$controller}");
            }

            $controllerInstance = new $controller();

            if (method_exists($controllerInstance, $method)) {
                // Передаем параметры маршрута в метод контроллера
                return call_user_func_array([$controllerInstance, $method], array_values($parameters));
            } else {
                error_log("Method not found: {$controller}::{$method}");
                throw new \Exception("Method {$method} not found in controller {$controller}");
            }
        } elseif (is_callable($action)) {
            return call_user_func_array($action, array_values($parameters));
        }

        throw new \Exception("Invalid route action: " . print_r($action, true));
    }

    private function show404()
    {
        http_response_code(404);

        $errorPage = __DIR__ . '/../../app/Views/errors/404.php';
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

    public function hasRoute($uri, $method = 'GET')
    {
        foreach ($this->routes as $route) {
            if ($route->matches($method, $uri)) {
                return true;
            }
        }
        return false;
    }
}