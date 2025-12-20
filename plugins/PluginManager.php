<?php

namespace Plugins;

class PluginManager
{
    private static $instance;
    private $plugins = [];
    private $activePlugins = [];

    // Удаляем неправильное использование ?? в старых версиях PHP
    private $pluginsDir;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        // Определяем путь к плагинам один раз
        $this->pluginsDir = $this->getPluginsDirectory();
    }

    public function loadPlugins()
    {
        error_log("PluginManager: Loading plugins from directory: " . $this->pluginsDir);

        // Проверяем существование директории
        if (!is_dir($this->pluginsDir)) {
            error_log("PluginManager: Plugins directory does not exist: " . $this->pluginsDir);
            return 0;
        }

        // Ищем плагины
        $pluginFolders = glob($this->pluginsDir . '*', GLOB_ONLYDIR);
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

                $this->plugins[$pluginName] = [
                    'path' => $pluginFolder,
                    'config' => $config,
                    'active' => false // По умолчанию неактивен
                ];

                error_log("Plugin {$pluginName}: registered successfully");
            } else {
                error_log("PluginManager: Plugin " . $pluginName . " missing required files");
            }
        }

        error_log("PluginManager: Total plugins registered: " . count($this->plugins));
        return count($this->plugins);
    }

    private function getPluginsDirectory()
    {
        // Базовый путь к плагинам
        $basePath = dirname(__DIR__);

        // Пробуем разные возможные пути
        $possiblePaths = [
            $basePath . '/plugins/',
            $basePath . '/../plugins/',
            __DIR__ . '/../../plugins/'
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        // Если ни один путь не найден, используем первый
        return $possiblePaths[0];
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

    public function activatePlugin($name)
    {
        if (isset($this->plugins[$name])) {
            $pluginData = $this->plugins[$name];
            $pluginFile = $pluginData['path'] . '/Plugin.php';

            error_log("Activating plugin: {$name}");
            error_log("Plugin file: {$pluginFile}");

            // Проверяем существование файла плагина
            if (!file_exists($pluginFile)) {
                error_log("Plugin file not found: {$pluginFile}");
                return false;
            }

            // Загружаем файл плагина
            try {
                require_once $pluginFile;

                // Формируем имя класса
                $className = "{$name}\\Plugin";
                error_log("Looking for class: {$className}");

                // Проверяем существование класса
                if (!class_exists($className)) {
                    // Пробуем альтернативное имя класса (без namespace)
                    $className = "Plugin{$name}";
                    error_log("Trying alternative class: {$className}");

                    if (!class_exists($className)) {
                        error_log("Class not found: {$className}");
                        return false;
                    }
                }

                // Создаем экземпляр плагина
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
                error_log("Exception trace: " . $e->getTraceAsString());
                return false;
            }
        }

        error_log("Plugin not found: {$name}");
        return false;
    }

    // Остальные методы остаются без изменений
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