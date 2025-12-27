<?php
declare(strict_types=1);

namespace App\Core\Container;

use Exception;
use ReflectionClass;
use ReflectionParameter;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * Dependency Injection Container
 *
 * A PSR-11 compliant dependency injection container that manages
 * class dependencies and implements inversion of control.
 *
 * @package App\Core\Container
 */
class Container implements ContainerInterface
{
    /**
     * @var array<string, array{concrete: mixed, shared: bool}> The container bindings
     */
    private array $bindings = [];

    /**
     * @var array<string, mixed> The shared instances
     */
    private array $instances = [];

    /**
     * @var array<string, string> The aliases
     */
    private array $aliases = [];

    /**
     * @var array<string, mixed> The resolved shared instances
     */
    private array $resolved = [];

    /**
     * Resolve an alias to its target.
     *
     * @param string $abstract The abstract type
     * @return string The resolved abstract type
     */
    private function resolveAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Check if a binding is shared.
     *
     * @param string $abstract The abstract type
     * @return bool True if shared
     */
    private function isShared(string $abstract): bool
    {
        return isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared'];
    }


    public function bind(string $abstract, $concrete = null, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete ?? $abstract,
            'shared' => $shared
        ];
    }

    public function singleton(string $abstract, $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }

    public function make(string $abstract, array $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for
     * @return mixed Entry
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new class($id) extends Exception implements NotFoundExceptionInterface {
                public function __construct($id) {
                    parent::__construct("No entry found for identifier: {$id}");
                }
            };
        }

        try {
            return $this->resolve($id);
        } catch (Exception $e) {
            throw new class($e->getMessage(), $e->getCode(), $e) extends Exception implements ContainerExceptionInterface {
            };
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for
     * @return bool
     */
    public function has($id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->instances[$id]) ||
               isset($this->bindings[$id]) ||
               class_exists($id) ||
               interface_exists($id);
    }

    public function call($callback, array $parameters = [])
    {
        if (is_array($callback)) {
            $reflection = new \ReflectionMethod($callback[0], $callback[1]);
        } elseif (is_object($callback) && !$callback instanceof \Closure) {
            $reflection = new \ReflectionMethod($callback, '__invoke');
        } else {
            $reflection = new \ReflectionFunction($callback);
        }

        $dependencies = $this->resolveDependencies($reflection->getParameters(), $parameters);

        return call_user_func_array($callback, $dependencies);
    }

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract The type to resolve
     * @param array $parameters Additional parameters to pass to the constructor
     * @return mixed The resolved instance
     * @throws Exception If the type cannot be resolved
     */
    private function resolve($abstract, array $parameters = [])
    {
        // Resolve aliases first
        $abstract = $this->resolveAlias($abstract);

        // Return existing instance if available
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Return resolved shared instance if available
        if (isset($this->resolved[$abstract]) && $this->isShared($abstract)) {
            return $this->resolved[$abstract];
        }

        // Get concrete implementation
        $concrete = $this->getConcrete($abstract);

        // Build the object
        $object = $this->build($concrete, $parameters);

        // Store shared instances
        if ($this->isShared($abstract)) {
            $this->resolved[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Get the concrete implementation for an abstract type.
     *
     * @param string $abstract The abstract type
     * @return mixed The concrete implementation
     */
    private function getConcrete(string $abstract)
    {
        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract]['concrete'];
            // If concrete is a closure, resolve it
            if ($concrete instanceof \Closure) {
                return $concrete($this);
            }
            return $concrete;
        }

        return $abstract;
    }

    /**
     * Build an object instance.
     *
     * @param mixed $concrete The concrete implementation
     * @param array $parameters Additional parameters to pass to the constructor
     * @return object The built instance
     * @throws Exception If the object cannot be built
     */
    private function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        if (!is_string($concrete)) {
            throw new Exception('Concrete must be a string or closure');
        }

        // Check if class exists
        if (!class_exists($concrete) && !interface_exists($concrete)) {
            throw new Exception("Class or interface {$concrete} does not exist");
        }

        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception("Class {$concrete} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve dependencies for a method or constructor.
     *
     * @param array $parameters The parameters to resolve
     * @param array $provided Additional parameters to use
     * @return array The resolved dependencies
     * @throws Exception If dependencies cannot be resolved
     */
    private function resolveDependencies(array $parameters, array $provided = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // Use provided parameter if available
            if (array_key_exists($name, $provided)) {
                $dependencies[] = $provided[$name];
                continue;
            }

            $type = $parameter->getType();

            // Handle typed parameters
            if ($type && !$type->isBuiltin()) {
                $class = $type->getName();

                // Check if parameter allows null
                $allowsNull = $parameter->allowsNull();

                try {
                    if ($this->has($class)) {
                        $dependencies[] = $this->resolve($class);
                    } elseif ($allowsNull) {
                        $dependencies[] = null;
                    } else {
                        throw new Exception("Cannot resolve dependency: {$class}");
                    }
                } catch (Exception $e) {
                    if ($allowsNull) {
                        $dependencies[] = null;
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } else {
                // Handle untyped or primitive parameters
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new Exception("Cannot resolve dependency: {$name}");
                }
            }
        }

        return $dependencies;
    }
}