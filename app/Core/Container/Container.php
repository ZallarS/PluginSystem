<?php
declare(strict_types=1);

namespace App\Core\Container;

class Container
{
    private $bindings = [];
    private $instances = [];

    public function bind($abstract, $concrete = null, $shared = false)
    {
        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function make($abstract, array $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);
        $object = $this->build($concrete, $parameters);

        if (isset($this->bindings[$abstract]['shared']) && $this->bindings[$abstract]['shared']) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    public function get($abstract)
    {
        return $this->make($abstract);
    }

    private function getConcrete($abstract)
    {
        if (isset($this->bindings[$abstract])) {
            return $this->bindings[$abstract]['concrete'];
        }

        return $abstract;
    }

    private function build($concrete, $parameters)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        $reflector = new \ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
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
            $name = $parameter->getName();

            if (array_key_exists($name, $provided)) {
                $dependencies[] = $provided[$name];
                continue;
            }

            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception("Cannot resolve dependency: {$name}");
            }
        }

        return $dependencies;
    }

    public function has($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }
}