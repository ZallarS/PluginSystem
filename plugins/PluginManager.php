<?php

namespace Plugins;

class PluginManager
{
    private static $instance;
    private $plugins = [];
    private $activePlugins = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function loadPlugins()
    {
        $pluginsDir = __DIR__;

        // Ищем плагины
        $pluginFolders = glob($pluginsDir . '/*', GLOB_ONLYDIR);

        foreach ($pluginFolders as $pluginFolder) {
            $pluginName = basename($pluginFolder);

            // Пропускаем служебные директории
            if ($pluginName === 'PluginManager.php' || $pluginName === 'ExamplePlugin') {
                continue;
            }

            $pluginFile = $pluginFolder . '/Plugin.php';
            $configFile = $pluginFolder . '/plugin.json';

            if (file_exists($pluginFile) && file_exists($configFile)) {
                // Загружаем конфиг
                $config = json_decode(file_get_contents($configFile), true);

                // Проверяем требования
                if (!$this->checkRequirements($config)) {
                    error_log("Plugin {$pluginName}: requirements not met");
                    continue;
                }

                $this->plugins[$pluginName] = [
                    'path' => $pluginFolder,
                    'config' => $config,
                    'active' => true // По умолчанию активен
                ];

                // Загружаем класс плагина
                $className = "{$pluginName}\\Plugin";
                if (class_exists($className)) {
                    try {
                        $pluginInstance = new $className();
                        $this->activePlugins[$pluginName] = $pluginInstance;

                        // Вызываем метод init если существует
                        if (method_exists($pluginInstance, 'init')) {
                            $pluginInstance->init();
                        }

                        error_log("Plugin {$pluginName}: loaded successfully");
                    } catch (\Exception $e) {
                        error_log("Plugin {$pluginName}: failed to load - " . $e->getMessage());
                    }
                }
            }
        }

        return count($this->activePlugins);
    }

    private function checkRequirements($config)
    {
        // Проверяем требования плагина
        if (isset($config['requires'])) {
            $requires = $config['requires'];

            // Проверка версии ядра
            if (isset($requires['core'])) {
                // Здесь можно добавить проверку версии ядра
                // Пока просто возвращаем true
                return true;
            }
        }

        return true;
    }

    public function getPlugins()
    {
        return $this->plugins;
    }

    public function getActivePlugins()
    {
        return $this->activePlugins;
    }

    public function getPlugin($name)
    {
        return $this->activePlugins[$name] ?? null;
    }

    public function activatePlugin($name)
    {
        if (isset($this->plugins[$name])) {
            $this->plugins[$name]['active'] = true;

            $className = "{$name}\\Plugin";
            if (class_exists($className)) {
                $this->activePlugins[$name] = new $className();
            }

            return true;
        }
        return false;
    }

    public function deactivatePlugin($name)
    {
        if (isset($this->plugins[$name])) {
            $this->plugins[$name]['active'] = false;
            unset($this->activePlugins[$name]);
            return true;
        }
        return false;
    }
}