<?php
// app/Core/Container/ContainerInterface.php
declare(strict_types=1);

namespace App\Core\Container;

interface ContainerInterface
{
    public function bind($abstract, $concrete = null, $shared = false);
    public function singleton($abstract, $concrete = null);
    public function make($abstract, array $parameters = []);
    public function get($id);
    public function has($id): bool;
}