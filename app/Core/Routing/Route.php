<?php
declare(strict_types=1);

namespace App\Core\Routing;

/**
 * Route class
 *
 * Represents a single route in the routing system.
 * Handles matching URIs and HTTP methods, and extracting parameters.
 *
 * @package App\Core\Routing
 */
class Route
{
    /**
     * @var string|array The HTTP method(s) for this route
     */
    private $method;

    /**
     * @var string The URI pattern for this route
     */
    private $uri;

    /**
     * @var mixed The action to execute when this route matches
     */
    private $action;

    /**
     * @var array The extracted parameters from the URI
     */
    private $parameters = [];


    /**
     * Create a new route instance.
     *
     * @param string|array $method The HTTP method(s) for the route
     * @param string $uri The URI pattern
     * @param mixed $action The action to execute when the route matches
     */
    public function __construct($method, $uri, $action)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->action = $action;
    }

    /**
     * Check if the route matches the given method and URI.
     *
     * @param string $method The HTTP method to check
     * @param string $uri The URI to match against
     * @return bool True if the route matches
     */
    public function matches($method, $uri)
    {
        // Check if method matches (supporting multiple methods)
        if (is_array($this->method)) {
            if (!in_array($method, $this->method)) {
                return false;
            }
        } else {
            if ($this->method !== $method) {
                return false;
            }
        }

        // Compile pattern and match against URI
        $pattern = $this->compilePattern($this->uri);
        $result = preg_match($pattern, $uri, $matches);

        if ($result) {
            $this->parameters = $this->extractParameters($matches);
            return true;
        }

        return false;
    }

    /**
     * Compile the URI pattern into a regular expression.
     *
     * Converts route parameters like {id} into named capture groups.
     *
     * @param string $uri The URI pattern
     * @return string The compiled regular expression
     */
    private function compilePattern($uri)
    {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    /**
     * Extract parameters from the regular expression matches.
     *
     * @param array $matches The matches from preg_match
     * @return array The extracted parameters
     */
    private function extractParameters($matches)
    {
        $params = [];
        foreach ($matches as $key => $value) {
            // Only include named parameters (string keys)
            if (is_string($key) && $key !== '0') {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Get the HTTP method(s) for this route.
     *
     * @return string|array The HTTP method(s)
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the URI pattern for this route.
     *
     * @return string The URI pattern
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the action for this route.
     *
     * @return mixed The route action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get all extracted parameters.
     *
     * @return array The parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get a specific parameter by name.
     *
     * @param string $name The parameter name
     * @param mixed $default The default value if parameter doesn't exist
     * @return mixed The parameter value or default
     */
    public function getParameter($name, $default = null)
    {
        return $this->parameters[$name] ?? $default;
    }
}