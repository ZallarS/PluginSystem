<?php

namespace Plugins;

use PDO;
use PDOException;

class PluginManager
{
    private static $instance;
    private $plugins = [];
    private $activePlugins = [];
    private $pluginsDir;
    private $db;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->pluginsDir = $this->getPluginsDirectory();
        $this->initDatabase();
        $this->loadActivePluginsFromDatabase();
    }

    private function initDatabase()
    {
        try {
            // Получаем конфигурацию БД
            $configPath = __DIR__ . '/../app/Config/database.php';
            if (file_exists($configPath)) {
                $config = require $configPath;
                $dbConfig = $config['connections']['mysql'] ?? $config['connections']['default'] ?? null;

                if ($dbConfig) {
                    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                    $this->db = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            error_log("PluginManager: Database connection failed - " . $e->getMessage());
            // Если БД недоступна, используем файловое хранилище как fallback
            $this->db = null;
        }
    }

    private function createPluginsTable()
    {
        if (!$this->db) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS active_plugins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                plugin_name VARCHAR(255) NOT NULL UNIQUE,
                activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_plugin_name (plugin_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            $this->db->exec($sql);
            error_log("PluginManager: Plugins table created or already exists");

        } catch (PDOException $e) {
            error_log("PluginManager: Failed to create plugins table - " . $e->getMessage());
        }
    }

    private function loadActivePluginsFromDatabase()
    {
        if ($this->db) {
            try {
                // Сначала создаем таблицу если не существует
                $this->createPluginsTable();

                $stmt = $this->db->query("SELECT plugin_name FROM active_plugins");
                $activePlugins = $stmt->fetchAll(PDO::FETCH_COLUMN);

                foreach ($activePlugins as $pluginName) {
                    $this->activePlugins[$pluginName] = null; // Заполняем, экземпляры создадим позже
                }

                error_log("PluginManager: Loaded " . count($activePlugins) . " active plugins from database");
            } catch (PDOException $e) {
                error_log("PluginManager: Failed to load active plugins from DB - " . $e->getMessage());
                $this->activePlugins = [];
            }
        } else {
            $this->activePlugins = [];
            error_log("PluginManager: Database not available, starting with empty active plugins");
        }
    }

    public function loadPlugins()
    {
        error_log("PluginManager: Loading plugins from directory: " . $this->pluginsDir);

        if (!is_dir($this->pluginsDir)) {
            error_log("PluginManager: Plugins directory does not exist: " . $this->pluginsDir);
            return 0;
        }

        $pluginFolders = glob($this->pluginsDir . '*', GLOB_ONLYDIR);
        error_log("PluginManager: Found " . count($pluginFolders) . " plugin folders");

        // ВАЖНО: Очищаем массив плагинов перед загрузкой
        $this->plugins = [];

        foreach ($pluginFolders as $pluginFolder) {
            $pluginName = basename($pluginFolder);

            if ($pluginName === '.' || $pluginName === '..') {
                continue;
            }

            error_log("PluginManager: Processing plugin: " . $pluginName);

            $pluginFile = $pluginFolder . '/Plugin.php';
            $configFile = $pluginFolder . '/plugin.json';

            if (file_exists($pluginFile) && file_exists($configFile)) {
                $configContent = file_get_contents($configFile);
                $config = json_decode($configContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Plugin {$pluginName}: Invalid JSON in config file - " . json_last_error_msg());
                    continue;
                }

                if (!$this->checkRequirements($config)) {
                    error_log("Plugin {$pluginName}: requirements not met");
                    continue;
                }

                // ПРОВЕРЯЕМ АКТИВНОСТЬ ПРАВИЛЬНО!
                // Проверяем, есть ли плагин в массиве активных плагинов
                $isActive = $this->isPluginActive($pluginName);

                // Дополнительно проверяем в БД
                if (!$isActive && $this->db) {
                    try {
                        $stmt = $this->db->prepare("SELECT COUNT(*) FROM active_plugins WHERE plugin_name = :plugin_name");
                        $stmt->execute([':plugin_name' => $pluginName]);
                        $isActive = $stmt->fetchColumn() > 0;
                    } catch (PDOException $e) {
                        error_log("PluginManager: Failed to check plugin activity in DB - " . $e->getMessage());
                    }
                }

                $this->plugins[$pluginName] = [
                    'path' => $pluginFolder,
                    'config' => $config,
                    'active' => $isActive
                ];

                // Если плагин активен, загружаем его экземпляр
                if ($isActive) {
                    $this->loadPluginInstance($pluginName);
                }

                error_log("Plugin {$pluginName}: registered successfully (active: " . ($isActive ? 'yes' : 'no') . ")");
            } else {
                error_log("PluginManager: Plugin " . $pluginName . " missing required files");
            }
        }

        error_log("PluginManager: Total plugins registered: " . count($this->plugins));
        return count($this->plugins);
    }

    private function checkRequirements($config)
    {
        if (isset($config['requires'])) {
            $requires = $config['requires'];

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

    private function loadPluginInstance($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            error_log("Plugin {$pluginName}: Plugin data not found");
            return false;
        }

        $pluginData = $this->plugins[$pluginName];
        $pluginFile = $pluginData['path'] . '/Plugin.php';

        if (!file_exists($pluginFile)) {
            error_log("Plugin {$pluginName}: Plugin file not found: {$pluginFile}");
            return false;
        }

        try {
            require_once $pluginFile;

            // Пробуем разные варианты имен классов
            $possibleClassNames = [
                "Plugins\\{$pluginName}\\Plugin",
                "Plugin{$pluginName}",
                "{$pluginName}\\Plugin",
                $pluginName . "\\Plugin"
            ];

            $pluginInstance = null;

            foreach ($possibleClassNames as $className) {
                if (class_exists($className)) {
                    error_log("Plugin {$pluginName}: Found class: {$className}");
                    $pluginInstance = new $className();
                    break;
                }
            }

            if (!$pluginInstance) {
                error_log("Plugin {$pluginName}: No valid class found");
                return false;
            }

            $this->activePlugins[$pluginName] = $pluginInstance;

            if (method_exists($pluginInstance, 'init')) {
                $pluginInstance->init();
            }

            error_log("Plugin {$pluginName}: instance loaded for active plugin");
            return true;

        } catch (\Exception $e) {
            error_log("Plugin {$pluginName}: failed to load instance - " . $e->getMessage());
            error_log("Plugin {$pluginName}: Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function activatePlugin($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            error_log("PluginManager: Plugin not found: {$pluginName}");
            return false;
        }

        // Если уже активен
        if ($this->isPluginActive($pluginName)) {
            error_log("PluginManager: Plugin already active: {$pluginName}");
            return true;
        }

        $pluginData = $this->plugins[$pluginName];
        $pluginFile = $pluginData['path'] . '/Plugin.php';

        error_log("PluginManager: Activating plugin: {$pluginName}");
        error_log("PluginManager: Plugin file path: {$pluginFile}");

        if (!file_exists($pluginFile)) {
            error_log("PluginManager: Plugin file not found: {$pluginFile}");
            return false;
        }

        try {
            require_once $pluginFile;

            // Пробуем разные варианты имен классов
            $possibleClassNames = [
                "Plugins\\{$pluginName}\\Plugin",
                "Plugin{$pluginName}",
                "{$pluginName}\\Plugin",
                $pluginName . "\\Plugin"
            ];

            $pluginInstance = null;

            foreach ($possibleClassNames as $className) {
                if (class_exists($className)) {
                    error_log("PluginManager: Found class: {$className}");
                    $pluginInstance = new $className();
                    break;
                } else {
                    error_log("PluginManager: Class not found: {$className}");
                }
            }

            if (!$pluginInstance) {
                error_log("PluginManager: Could not find any valid class for plugin: {$pluginName}");
                return false;
            }

            // ВАЖНО: Добавляем в активные плагины
            $this->activePlugins[$pluginName] = $pluginInstance;

            // ВАЖНО: Обновляем статус в массиве плагинов
            $this->plugins[$pluginName]['active'] = true;

            if (method_exists($pluginInstance, 'activate')) {
                $pluginInstance->activate();
            }

            if (method_exists($pluginInstance, 'init')) {
                $pluginInstance->init();
            }

            $this->saveActivePluginToDatabase($pluginName);

            error_log("PluginManager: Plugin {$pluginName}: Activated and saved to database");
            error_log("PluginManager: Active plugins count after activation: " . count($this->activePlugins));
            return true;

        } catch (\Exception $e) {
            error_log("PluginManager: Plugin {$pluginName}: Failed to activate - " . $e->getMessage());
            error_log("PluginManager: Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    public function deactivatePlugin($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            error_log("PluginManager: Plugin not found: {$pluginName}");
            return false;
        }

        if (isset($this->activePlugins[$pluginName]) && method_exists($this->activePlugins[$pluginName], 'deactivate')) {
            $this->activePlugins[$pluginName]->deactivate();
        }

        $this->plugins[$pluginName]['active'] = false;
        unset($this->activePlugins[$pluginName]);

        $this->removeActivePluginFromDatabase($pluginName);

        error_log("PluginManager: Plugin {$pluginName}: Deactivated and removed from database");
        return true;
    }

    private function saveActivePluginToDatabase($pluginName)
    {
        if (!$this->db) {
            error_log("PluginManager: Database not available, cannot save plugin: {$pluginName}");
            return false;
        }

        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO active_plugins (plugin_name) VALUES (:plugin_name)");
            $stmt->execute([':plugin_name' => $pluginName]);
            return true;
        } catch (PDOException $e) {
            error_log("PluginManager: Failed to save active plugin to DB - " . $e->getMessage());
            return false;
        }
    }

    private function removeActivePluginFromDatabase($pluginName)
    {
        if (!$this->db) {
            error_log("PluginManager: Database not available, cannot remove plugin: {$pluginName}");
            return false;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM active_plugins WHERE plugin_name = :plugin_name");
            $stmt->execute([':plugin_name' => $pluginName]);
            return true;
        } catch (PDOException $e) {
            error_log("PluginManager: Failed to remove active plugin from DB - " . $e->getMessage());
            return false;
        }
    }

    private function getPluginsDirectory()
    {
        $basePath = dirname(__DIR__);

        $possiblePaths = [
            $basePath . '/plugins/',
            $basePath . '/../plugins/',
            __DIR__ . '/../../plugins/'
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                return rtrim($path, '/') . '/';
            }
        }

        return $possiblePaths[0];
    }

    public function getPlugins()
    {
        error_log("PluginManager: Getting plugins list");
        error_log("PluginManager: Total plugins in array: " . count($this->plugins));

        foreach ($this->plugins as $name => &$pluginData) {
            // ОБЯЗАТЕЛЬНО обновляем статус из реальных данных
            $pluginData['active'] = $this->isPluginActive($name);
            error_log("PluginManager: Plugin {$name} status: " . ($pluginData['active'] ? 'active' : 'inactive'));
        }
        unset($pluginData); // Разрываем ссылку

        error_log("PluginManager: Returning " . count($this->plugins) . " plugins");
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

    public function isPluginActive($name)
    {
        // Сначала проверяем в массиве активных плагинов (учитываем и null и объект)
        if (isset($this->activePlugins[$name]) || array_key_exists($name, $this->activePlugins)) {
            error_log("PluginManager: Plugin {$name} is active in activePlugins array");
            return true;
        }

        // Затем проверяем в массиве всех плагинов
        if (isset($this->plugins[$name]['active']) && $this->plugins[$name]['active'] === true) {
            error_log("PluginManager: Plugin {$name} is active in plugins array");
            return true;
        }

        // И наконец проверяем в БД
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM active_plugins WHERE plugin_name = :plugin_name");
                $stmt->execute([':plugin_name' => $name]);
                $count = $stmt->fetchColumn();
                $isActive = $count > 0;

                if ($isActive) {
                    error_log("PluginManager: Plugin {$name} is active in database (count: {$count})");
                }

                return $isActive;
            } catch (PDOException $e) {
                error_log("PluginManager: Failed to check plugin activity in DB - " . $e->getMessage());
            }
        }

        error_log("PluginManager: Plugin {$name} is NOT active");
        return false;
    }

    public function pluginExists($name)
    {
        return isset($this->plugins[$name]);
    }

    public function getPluginInfo($name)
    {
        return $this->plugins[$name] ?? null;
    }

    public function clearActivePlugins()
    {
        $this->activePlugins = [];

        if ($this->db) {
            try {
                $this->db->exec("DELETE FROM active_plugins");
                error_log("PluginManager: Cleared all active plugins from database");
            } catch (PDOException $e) {
                error_log("PluginManager: Failed to clear active plugins from DB - " . $e->getMessage());
            }
        }
    }
    public function reloadActivePlugins()
    {
        $this->activePlugins = [];
        $this->loadActivePluginsFromDatabase();

        // Перезагружаем экземпляры активных плагинов
        foreach ($this->activePlugins as $pluginName => $value) {
            if ($value === null && isset($this->plugins[$pluginName])) {
                $this->loadPluginInstance($pluginName);
            }
        }
    }
}