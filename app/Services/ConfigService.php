<?php
declare(strict_types=1);

namespace App\Services;

/**
 * ConfigService class
 *
 * Provides configuration management for the application.
 * Loads configuration files and provides dot notation access.
 *
 * @package App\Services
 */
class ConfigService
{
    /**
     * @var array The loaded configuration
     */
    private array $config = [];

    /**
     * @var string The path to the configuration directory
     */
    private string $configPath;


    /**
     * Create a new configuration service instance.
     *
     * @param string|null $configPath The path to the configuration directory
     */
    public function __construct(string $configPath = null)
    {
        $this->configPath = $configPath ?? config_path();
        $this->loadAllConfigs();
    }

    /**
     * Load all configuration files from the configuration directory.
     *
     * @return void
     */
    private function loadAllConfigs(): void
    {
        $files = glob($this->configPath . '/*.php');

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $this->config[$key] = require $file;
        }
    }

    /**
     * Get a configuration value by key.
     *
     * Supports dot notation for nested configuration (e.g. 'database.host').
     *
     * @param string $key The configuration key
     * @param mixed $default The default value if key not found
     * @return mixed The configuration value or default
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $segment) {
            if (is_array($value) && isset($value[$segment])) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Get all configuration values.
     *
     * @return array All configuration values
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Set a configuration value by key.
     *
     * Supports dot notation for nested configuration (e.g. 'database.host').
     *
     * @param string $key The configuration key
     * @param mixed $value The value to set
     * @return void
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }

        $config = $value;
    }
}