<?php

return [
    /**
     * Application environment.
     *
     * Determines the current environment (production, development, testing, etc.).
     * Used to configure services differently based on environment.
     */
    'env' => env('APP_ENV', 'production'),

    /**
     * Application debug mode.
     *
     * When enabled, detailed error messages with stack traces are shown.
     * When disabled, generic error pages are shown for security.
     */
    'debug' => env('APP_DEBUG', false),

    /**
     * Application URL.
     *
     * Used by console commands to generate proper URLs.
     * Should be set to the root URL of the application.
     */
    'url' => env('APP_URL', 'http://localhost'),

    /**
     * Application timezone.
     *
     * The default timezone used by PHP date and datetime functions.
     * Set to Moscow time as the default.
     */
    'timezone' => 'Europe/Moscow',

    /**
     * Widget cache settings.
     *
     * Configuration for caching widget rendering to improve performance.
     * Includes enabling/disabling cache and setting time-to-live.
     */
    'widget_cache' => [
        'enabled' => env('WIDGET_CACHE_ENABLED', true),
        'ttl' => env('WIDGET_CACHE_TTL', 300),
    ],

    /**
     * Session settings.
     *
     * Configuration for PHP session handling including
     * lifetime and cookie name.
     */
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 120),
        'cookie' => env('SESSION_COOKIE', 'mvc_session'),
    ],

    /**
     * Database settings.
     *
     * Configuration for database connections including
     * default connection and connection details.
     */
    'database' => [
        'default' => env('DB_CONNECTION', 'mysql'),
        'connections' => [
            'mysql' => [
                'host' => env('DB_HOST', 'localhost'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'SystemPlugins'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
        ],
    ],
];