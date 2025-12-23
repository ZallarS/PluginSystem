<?php
declare(strict_types=1);

namespace App\Services;

class ConfigService
{
    private array $config = [];
    private string $configPath;

    public function __construct(string $configPath = null)
    {
        $this->configPath = $configPath ?? config_path();
        $this->loadAllConfigs();
    }

    private function loadAllConfigs(): void
    {
        $files = glob($this->configPath . '/*.php');

        foreach ($files as $file) {
            $key = pathinfo($file, PATHINFO_FILENAME);
            $this->config[$key] = require $file;
        }
    }

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

    public function all(): array
    {
        return $this->config;
    }

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