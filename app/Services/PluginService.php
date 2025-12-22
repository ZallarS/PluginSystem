<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HookManager;

class PluginService
{
    private $pluginManager;
    private HookManager $hookManager;

    public function __construct($pluginManager = null, HookManager $hookManager = null)
    {
        if ($pluginManager && class_exists('Plugins\PluginManager')) {
            $this->pluginManager = $pluginManager;
        } else {
            $this->pluginManager = \Plugins\PluginManager::getInstance();
        }

        $this->hookManager = $hookManager ?? HookManager::getInstance();
    }

    public function getAllPlugins(): array
    {
        return $this->pluginManager->getPlugins();
    }

    public function getActivePlugins(): array
    {
        return $this->pluginManager->getActivePlugins();
    }

    public function activatePlugin(string $pluginName): bool
    {
        $result = $this->pluginManager->activatePlugin($pluginName);

        if ($result) {
            $this->hookManager->doAction('plugin_activated', $pluginName);
        }

        return $result;
    }

    public function deactivatePlugin(string $pluginName): bool
    {
        $result = $this->pluginManager->deactivatePlugin($pluginName);

        if ($result) {
            $this->hookManager->doAction('plugin_deactivated', $pluginName);
        }

        return $result;
    }

    public function pluginExists(string $pluginName): bool
    {
        return $this->pluginManager->pluginExists($pluginName);
    }

    public function reloadPlugins(): void
    {
        $this->pluginManager->reloadActivePlugins();
    }

    public function getPluginInfo(string $pluginName): ?array
    {
        $plugins = $this->getAllPlugins();
        return $plugins[$pluginName] ?? null;
    }

    public function initializePlugin(string $pluginName): bool
    {
        $plugin = $this->pluginManager->getPlugin($pluginName);

        if ($plugin && method_exists($plugin, 'init')) {
            try {
                $plugin->init();
                return true;
            } catch (\Exception $e) {
                error_log("Failed to initialize plugin {$pluginName}: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }
}