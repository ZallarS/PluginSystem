<?php

return [
    /**
     * Plugins enabled.
     *
     * Determines whether the plugin system is enabled.
     * Set to true to allow plugin loading and management.
     */
    'enabled' => true,

    /**
     * Plugins path.
     *
     * The filesystem path where plugins are stored.
     * Uses the project root's plugins directory.
     */
    'path' => dirname(__DIR__, 2) . '/plugins', // Правильный путь к плагинам

    /**
     * Auto discover plugins.
     *
     * When enabled, plugins are automatically discovered
     * and loaded on application startup.
     */
    'auto_discover' => true,

    /**
     * Default plugins.
     *
     * List of plugins that should be enabled by default.
     * Currently empty, allowing manual activation.
     */
    'default_plugins' => [],
];
