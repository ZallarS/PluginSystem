<?php

namespace Core\Router;

class RouteCollection
{
    private $routes = [];

    public function add(Route $route)
    {
        $this->routes[] = $route;
    }

    public function all()
    {
        return $this->routes;
    }

    public function get($uri, $action)
    {
        $this->add(new Route('GET', $uri, $action));
    }

    public function post($uri, $action)
    {
        $this->add(new Route('POST', $uri, $action));
    }

    public function match($methods, $uri, $action)
    {
        foreach ((array)$methods as $method) {
            $this->add(new Route($method, $uri, $action));
        }
    }
}