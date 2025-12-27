<?php

    return [
        /**
         * Widget cache settings.
         *
         * Configuration for caching rendered widgets to improve performance.
         */
        'cache' => [
            /**
             * Cache enabled.
             *
             * Whether widget caching is enabled.
             * Can be controlled by WIDGET_CACHE_ENABLED environment variable.
             */
            'enabled' => env('WIDGET_CACHE_ENABLED', true),

            /**
             * Cache time-to-live.
             *
             * Number of seconds before cached widget expires.
             * Defaults to 300 seconds (5 minutes).
             */
            'ttl' => env('WIDGET_CACHE_TTL', 300), // 5 минут

            /**
             * Cache storage path.
             *
             * Directory where cached widget HTML is stored.
             */
            'path' => storage_path('cache/widgets'),
        ],

        /**
         * Default widgets.
         *
         * Configuration for widgets that are enabled by default for users.
         */
        'default_widgets' => [
            /**
             * System statistics widget.
             *
             * Shows system performance metrics by default.
             */
            'system_stats' => true,
            // Добавить другие виджеты по умолчанию
        ],

        /**
         * Security settings.
         *
         * Configuration for widget security and limitations.
         */
        'security' => [
            /**
             * Maximum widgets per user.
             *
             * Limits the number of widgets a user can have to prevent abuse.
             */
            'max_widgets_per_user' => 50,

            /**
             * Allowed widget sizes.
             *
             * Defines which sizes are allowed for widgets (small, medium, large).
             */
            'allowed_sizes' => ['small', 'medium', 'large'],
        ],
    ];
