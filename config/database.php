<?php

return [
    /**
     * Default database connection.
     *
     * Specifies which connection to use by default.
     * Set to 'mysql' as the primary database driver.
     */
    'default' => 'mysql',

    /**
     * Database connections.
     *
     * Configuration for all available database connections.
     * Currently only MySQL is configured.
     */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'SystemPlugins'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
    ],
];
