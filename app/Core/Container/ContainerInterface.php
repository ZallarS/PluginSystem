<?php
declare(strict_types=1);

namespace App\Core\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Bind an abstract type to a concrete implementation.
     *
     * @param string $abstract The abstract type (interface or class name)
     * @param mixed $concrete The concrete implementation (class name, closure, or instance)
     * @param bool $shared Whether the binding should be shared (singleton)
     * @return void
     */
    public function bind(string $abstract, $concrete = null, bool $shared = false): void;

    /**
     * Register a shared binding in the container.
     *
     * @param string $abstract The abstract type
     * @param mixed $concrete The concrete implementation
     * @return void
     */
    public function singleton(string $abstract, $concrete = null): void;

    /**
     * Register an existing instance as a shared binding.
     *
     * @param string $abstract The abstract type
     * @param mixed $instance The instance to register
     * @return void
     */
    public function instance(string $abstract, $instance): void;

    /**
     * Create an alias for an abstract type.
     *
     * @param string $abstract The original abstract type
     * @param string $alias The alias name
     * @return void
     */
    public function alias(string $abstract, string $alias): void;

    /**
     * Resolve the given type from the container.
     *
     * @param string $abstract The type to resolve
     * @param array $parameters Additional parameters to pass to the constructor
     * @return mixed The resolved instance
     * @throws Exception If the type cannot be resolved
     */
    public function make(string $abstract, array $parameters = []);

    /**
     * Get an instance from the container.
     *
     * @param string $id The ID of the instance to resolve
     * @return mixed The resolved instance
     * @throws Exception If the instance cannot be resolved
     */
    public function get($id);

    /**
     * Determine if the given abstract type has been bound.
     *
     * @param string $id The ID to check
     * @return bool True if the type is bound
     */
    public function has($id): bool;

    /**
     * Call a callback with dependencies resolved.
     *
     * @param callable $callback The callback to call
     * @param array $parameters Additional parameters to pass to the callback
     * @return mixed The result of the callback
     */
    public function call($callback, array $parameters = []);
}