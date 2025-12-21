<?php

return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../../storage/cache',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
        ],
    ],

    'prefix' => 'mvc_',
];