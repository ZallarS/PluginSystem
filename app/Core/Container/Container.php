<?php
declare(strict_types=1);

namespace App\Core\Container;

use Exception;
use ReflectionClass;
use ReflectionParameter;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];
    private array $resolved = [];

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

    public function get($id)
    {
        return $this->resolve($id);
    }

    public function has($id): bool
    {
        $id = $this->aliases[$id] ?? $id;

        return isset($this->bindings[$id]) ||
            isset($this->instances[$id]) ||
            class_exists($id);
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

    private function resolve($abstract, array $parameters = [])
    {
        $abstract = $this->aliases[$abstract] ?? $abstract;

        // Если уже есть инстанс, возвращаем его
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Если уже разрешено и shared, возвращаем из resolved
        if (isset($this->resolved[$abstract]) &&
            isset($this->bindings[$abstract]['shared']) &&
            $this->bindings[$abstract]['shared']) {
            return $this->resolved[$abstract];
        }

        // Получаем конкретную реализацию
        $concrete = $this->getConcrete($abstract);

        // Создаем объект
        $object = $this->build($concrete, $parameters);

        // Сохраняем в resolved если shared
        if (isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared']) {
            $this->resolved[$abstract] = $object;
        }

        return $object;
    }

    private function getConcrete(string $abstract)
    {
        return $this->bindings[$abstract]['concrete'] ?? $abstract;
    }

    private function build($concrete, array $parameters = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
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

    private function resolveDependencies(array $parameters, array $provided = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();

            if (!$dependency || $dependency->isBuiltin()) {
                // Примитивный тип или без типа
                $name = $parameter->getName();

                if (array_key_exists($name, $provided)) {
                    $dependencies[] = $provided[$name];
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new Exception("Cannot resolve dependency: {$name}");
                }
            } else {
                // Объектный тип
                $class = $dependency->getName();
                $dependencies[] = $this->resolve($class);
            }
        }

        return $dependencies;
    }
}