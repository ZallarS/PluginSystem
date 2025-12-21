<?php

return [
    'name' => 'My MVC System',
    'env' => 'development',
    'debug' => true,
    'url' => 'https://lib31.ru',
    'timezone' => 'Europe/Moscow',
    'locale' => 'ru',
    'key' => 'base64:your-secret-key-here',

    'providers' => [
        // Core providers
        Core\Database\DatabaseServiceProvider::class,
        Core\Session\SessionServiceProvider::class,
        Core\Cache\CacheServiceProvider::class,
        Core\Security\SecurityServiceProvider::class,

        // App providers
        App\Providers\AppServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ],
];