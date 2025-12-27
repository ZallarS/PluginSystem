<?php

return [
    /**
     * Default cache store.
     *
     * Specifies which cache driver to use by default.
     * Set to 'file' for file-based caching.
     */
    'default' => 'file',

    /**
     * Cache stores.
     *
     * Configuration for different cache storage options.
     * Supports file system and Redis caching.
     */
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

    /**
     * Cache key prefix.
     *
     * Prefix added to all cache keys to prevent collisions.
     * Set to 'mvc_' to identify keys from this application.
     */
    'prefix' => 'mvc_',
];
