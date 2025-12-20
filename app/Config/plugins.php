<?php

return [
    'enabled' => env('PLUGINS_ENABLED', true),
    'path' => env('PLUGINS_PATH', plugins_path()),
    'auto_discover' => env('PLUGINS_AUTO_DISCOVER', true),

    'default_plugins' => [
        // Пример плагина
        // 'ExamplePlugin' => [
        //     'enabled' => true,
        //     'priority' => 10,
        // ],
    ],

    'hooks' => [
        'init' => [],
        'before_routing' => [],
        'after_routing' => [],
        'before_controller' => [],
        'after_controller' => [],
        'before_response' => [],
        'after_response' => [],
    ],
];