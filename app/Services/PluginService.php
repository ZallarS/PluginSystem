<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\HookManager;

/**
 * PluginService class
 *
 * Provides a service layer for plugin management with additional
 * functionality like hook firing and plugin initialization.
 *
 * @package App\Services
 */
class PluginService
{
    /**
     * @var \Plugins\PluginManager The plugin manager instance
     */
    private $pluginManager;

    /**
     * @var HookManager The hook manager instance
     */
    private HookManager $hookManager;


    /**
     * Create a new plugin service instance.
     *
     * @param \Plugins\PluginManager $pluginManager The plugin manager
     * @param HookManager $hookManager The hook manager
     */
    public function __construct(
        private \Plugins\PluginManager $pluginManager,
        private HookManager $hookManager
    ) {
    }

    /**
     * Get all available plugins.
     *
     * @return array The plugins array
     */
    public function getAllPlugins(): array
    {
        return $this->pluginManager->getPlugins();
    }

    /**
     * Get all active plugins.
     *
     * @return array The active plugins array
     */
    public function getActivePlugins(): array
    {
        return $this->pluginManager->getActivePlugins();
    }

    /**
     * Activate a plugin and fire the activation hook.
     *
     * @param string $pluginName The plugin name to activate
     * @return bool True if activation was successful
     */
    public function activatePlugin(string $pluginName): bool
    {
        $result = $this->pluginManager->activatePlugin($pluginName);

        if ($result) {
            $this->hookManager->doAction('plugin_activated', $pluginName);
        }

        return $result;
    }

    /**
     * Deactivate a plugin and fire the deactivation hook.
     *
     * @param string $pluginName The plugin name to deactivate
     * @return bool True if deactivation was successful
     */
    public function deactivatePlugin(string $pluginName): bool
    {
        $result = $this->pluginManager->deactivatePlugin($pluginName);

        if ($result) {
            $this->hookManager->doAction('plugin_deactivated', $pluginName);
        }

        return $result;
    }

    /**
     * Check if a plugin exists.
     *
     * @param string $pluginName The plugin name
     * @return bool True if the plugin exists
     */
    public function pluginExists(string $pluginName): bool
    {
        return $this->pluginManager->pluginExists($pluginName);
    }

    /**
     * Reload all active plugins.
     *
     * Clears and reloads active plugins from storage.
     *
     * @return void
     */
    public function reloadPlugins(): void
    {
        $this->pluginManager->reloadActivePlugins();
    }

    /**
     * Get information about a specific plugin.
     *
     * @param string $pluginName The plugin name
     * @return array|null The plugin information or null if not found
     */
    public function getPluginInfo(string $pluginName): ?array
    {
        $plugins = $this->getAllPlugins();
        return $plugins[$pluginName] ?? null;
    }

    /**
     * Initialize a plugin by calling its init method.
     *
     * @param string $pluginName The plugin name
     * @return bool True if initialization was successful
     */
    public function initializePlugin(string $pluginName): bool
    {
        $plugin = $this->pluginManager->getPlugin($pluginName);

        if ($plugin && method_exists($plugin, 'init')) {
            try {
                $plugin->init();
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
}
