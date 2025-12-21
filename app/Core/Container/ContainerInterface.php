<?php
declare(strict_types=1);

namespace App\Core\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    public function bind(string $abstract, $concrete = null, bool $shared = false): void;
    public function singleton(string $abstract, $concrete = null): void;
    public function instance(string $abstract, $instance): void;
    public function alias(string $abstract, string $alias): void;
    public function make(string $abstract, array $parameters = []);
    public function get($id);
    public function has($id): bool;
    public function call($callback, array $parameters = []);
}