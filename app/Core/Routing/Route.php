<?php
declare(strict_types=1);

namespace App\Core\Routing;

class Route
{
    private $method;
    private $uri;
    private $action;
    private $parameters = [];

    public function __construct($method, $uri, $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
    }

    public function matches($method, $uri)
    {
        if ($this->method !== $method) {
            return false;
        }

        $pattern = $this->compilePattern($this->uri);
        $result = preg_match($pattern, $uri, $matches);

        if ($result) {
            $this->parameters = $this->extractParameters($matches);
            return true;
        }

        return false;
    }

    private function compilePattern($uri)
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    private function extractParameters($matches)
    {
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }
}