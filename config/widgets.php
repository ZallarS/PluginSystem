<?php

    return [
        'cache' => [
            'enabled' => env('WIDGET_CACHE_ENABLED', true),
            'ttl' => env('WIDGET_CACHE_TTL', 300), // 5 минут
            'path' => storage_path('cache/widgets'),
        ],

        'default_widgets' => [
            'system_stats' => true,
            // Добавить другие виджеты по умолчанию
        ],

        'security' => [
            'max_widgets_per_user' => 50,
            'allowed_sizes' => ['small', 'medium', 'large'],
        ],
    ];