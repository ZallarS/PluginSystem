<?php

namespace Core\Container;

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
        // Если уже есть экземпляр singleton, возвращаем его
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Получаем конкрентную реализацию
        $concrete = $this->getConcrete($abstract);

        // Создаем объект
        $object = $this->build($concrete, $parameters);

        // Если это singleton, сохраняем экземпляр
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
        // Если это функция-замыкание, вызываем ее
        if ($concrete instanceof \Closure) {
            return $concrete($this, $parameters);
        }

        // Создаем рефлексию класса
        $reflector = new \ReflectionClass($concrete);

        // Проверяем, можно ли создать экземпляр
        if (!$reflector->isInstantiable()) {
            throw new \Exception("Class {$concrete} is not instantiable");
        }

        // Получаем конструктор
        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            // Нет конструктора
            return new $concrete();
        }

        // Получаем параметры конструктора
        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        // Создаем экземпляр с зависимостями
        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $parameters, array $provided = [])
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            // Если параметр предоставлен явно, используем его
            if (array_key_exists($name, $provided)) {
                $dependencies[] = $provided[$name];
                continue;
            }

            // Получаем тип параметра
            $type = $parameter->getType();

            if ($type && !$type->isBuiltin()) {
                // Это класс, разрешаем его через контейнер
                $dependencies[] = $this->make($type->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                // Используем значение по умолчанию
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