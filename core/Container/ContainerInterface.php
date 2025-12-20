<?php

namespace Core\Container;

interface ContainerInterface
{
    public function bind($abstract, $concrete = null, $shared = false);
    public function singleton($abstract, $concrete = null);
    public function make($abstract, array $parameters = []);
    public function get($abstract);
    public function has($abstract);
}