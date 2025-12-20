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
        // ИСПРАВЛЕНО: Получаем путь к плагинам из конфигурации
        $pluginsDir = $this->getPluginsDirectory();

        error_log("PluginManager: Loading plugins from directory: " . $pluginsDir);

        // Проверяем существование директории
        if (!is_dir($pluginsDir)) {
            error_log("PluginManager: Plugins directory does not exist: " . $pluginsDir);
            return 0;
        }

        // Ищем плагины
        $pluginFolders = glob($pluginsDir . '*', GLOB_ONLYDIR);
        error_log("PluginManager: Found " . count($pluginFolders) . " plugin folders");

        foreach ($pluginFolders as $pluginFolder) {
            $pluginName = basename($pluginFolder);

            // Пропускаем служебные директории
            if ($pluginName === '.' || $pluginName === '..') {
                continue;
            }

            error_log("PluginManager: Processing plugin: " . $pluginName);

            $pluginFile = $pluginFolder . '/Plugin.php';
            $configFile = $pluginFolder . '/plugin.json';

            if (file_exists($pluginFile) && file_exists($configFile)) {
                error_log("PluginManager: Plugin " . $pluginName . " has required files");

                // Загружаем конфиг
                $configContent = file_get_contents($configFile);
                $config = json_decode($configContent, true);

                // Проверяем валидность JSON
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Plugin {$pluginName}: Invalid JSON in config file - " . json_last_error_msg());
                    continue;
                }

                // Проверяем требования
                if (!$this->checkRequirements($config)) {
                    error_log("Plugin {$pluginName}: requirements not met");
                    continue;
                }

                // Регистрируем автозагрузку для этого плагина
                $this->registerPluginAutoloader($pluginName, $pluginFolder);

                $this->plugins[$pluginName] = [
                    'path' => $pluginFolder,
                    'config' => $config,
                    'active' => false // По умолчанию неактивен
                ];

                error_log("Plugin {$pluginName}: registered successfully");
            } else {
                error_log("PluginManager: Plugin " . $pluginName . " missing required files");
                if (!file_exists($pluginFile)) {
                    error_log("PluginManager: Missing Plugin.php");
                }
                if (!file_exists($configFile)) {
                    error_log("PluginManager: Missing plugin.json");
                }
            }
        }

        error_log("PluginManager: Total plugins registered: " . count($this->plugins));
        return count($this->plugins);
    }

    private function getPluginsDirectory()
    {
        // Пытаемся получить путь из конфигурации
        $configPath = __DIR__ . '/../../app/Config/plugins.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            if (isset($config['path'])) {
                return rtrim($config['path'], '/') . '/';
            }
        }

        // По умолчанию используем базовый путь
        return dirname(__DIR__) . '/plugins/';
    }

    private function registerPluginAutoloader($pluginName, $pluginFolder)
    {
        spl_autoload_register(function ($className) use ($pluginName, $pluginFolder) {
            // Если класс принадлежит этому плагину
            if (strpos($className, $pluginName . '\\') === 0) {
                $relativeClass = substr($className, strlen($pluginName) + 1);
                $file = $pluginFolder . '/' . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }
            return false;
        });
    }

    private function checkRequirements($config)
    {
        // Проверяем требования плагина
        if (isset($config['requires'])) {
            $requires = $config['requires'];

            // Проверка версии PHP
            if (isset($requires['php'])) {
                $requiredVersion = $requires['php'];
                $currentVersion = PHP_VERSION;

                if (!version_compare($currentVersion, $requiredVersion, '>=')) {
                    error_log("Plugin requirement failed: PHP {$currentVersion} < {$requiredVersion}");
                    return false;
                }
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
        if (isset($this->activePlugins[$name])) {
            return $this->activePlugins[$name];
        }
        return null;
    }

    public function activatePlugin($name)
    {
        if (isset($this->plugins[$name])) {
            $pluginData = $this->plugins[$name];

            // Загружаем класс плагина
            $className = "{$name}\\Plugin";

            if (!class_exists($className)) {
                // Пытаемся загрузить файл вручную
                $pluginFile = $pluginData['path'] . '/Plugin.php';
                if (file_exists($pluginFile)) {
                    require_once $pluginFile;
                }
            }

            if (class_exists($className)) {
                try {
                    $pluginInstance = new $className();
                    $this->activePlugins[$name] = $pluginInstance;
                    $this->plugins[$name]['active'] = true;

                    // Вызываем метод init если существует
                    if (method_exists($pluginInstance, 'init')) {
                        $pluginInstance->init();
                    }

                    error_log("Plugin {$name}: activated successfully");
                    return true;
                } catch (\Exception $e) {
                    error_log("Plugin {$name}: failed to activate - " . $e->getMessage());
                }
            } else {
                error_log("Plugin {$name}: class {$className} not found");
            }
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