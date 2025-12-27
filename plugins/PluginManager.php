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

    public function loadSystemPlugins()
    {
        $systemPluginsPath = dirname(__DIR__) . '/plugins/SystemWidgets';

        if (file_exists($systemPluginsPath . '/plugin.json')) {
            $config = json_decode(file_get_contents($systemPluginsPath . '/plugin.json'), true);

            if ($config && isset($config['namespace'], $config['class'])) {
                $className = $config['namespace'] . '\\' . $config['class'];
                $pluginFile = $systemPluginsPath . '/' . $config['class'] . '.php';

                if (file_exists($pluginFile)) {
                    require_once $pluginFile;

                    if (class_exists($className)) {
                        $this->plugins[$config['name']] = [
                            'config' => $config,
                            'instance' => new $className(),
                            'active' => true
                        ];

                        // Инициализируем плагин
                        $this->plugins[$config['name']]['instance']->init();

                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function initDatabase()
    {
        try {
            // Получаем конфигурацию БД
            $configPath = config_path('database.php');
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

        } catch (PDOException $e) {
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

            } catch (PDOException $e) {
                $this->activePlugins = [];
            }
        } else {
            $this->activePlugins = [];
        }
    }

    public function loadPlugins()
    {

        if (!is_dir($this->pluginsDir)) {
            return 0;
        }

        $pluginFolders = glob($this->pluginsDir . '*', GLOB_ONLYDIR);

        // ВАЖНО: Очищаем массив плагинов перед загрузкой
        $this->plugins = [];

        foreach ($pluginFolders as $pluginFolder) {
            $pluginName = basename($pluginFolder);

            if ($pluginName === '.' || $pluginName === '..') {
                continue;
            }

            $pluginFile = $pluginFolder . '/Plugin.php';
            $configFile = $pluginFolder . '/plugin.json';

            if (file_exists($pluginFile) && file_exists($configFile)) {
                $configContent = file_get_contents($configFile);
                $config = json_decode($configContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                if (!$this->checkRequirements($config)) {
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

            } else {
            }
        }
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
                    return false;
                }
            }
        }

        return true;
    }

    private function loadPluginInstance($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            return false;
        }

        $pluginData = $this->plugins[$pluginName];
        $pluginFile = $pluginData['path'] . '/Plugin.php';

        if (!file_exists($pluginFile)) {
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
                    $pluginInstance = new $className();
                    break;
                }
            }

            if (!$pluginInstance) {
                return false;
            }

            $this->activePlugins[$pluginName] = $pluginInstance;

            if (method_exists($pluginInstance, 'init')) {
                $pluginInstance->init();
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function activatePlugin($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            return false;
        }

        // Если уже активен
        if ($this->isPluginActive($pluginName)) {
            return true;
        }

        $pluginData = $this->plugins[$pluginName];
        $pluginFile = $pluginData['path'] . '/Plugin.php';

        if (!file_exists($pluginFile)) {
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
                    $pluginInstance = new $className();
                    break;
                } else {
                }
            }

            if (!$pluginInstance) {
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

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function deactivatePlugin($pluginName)
    {
        if (!isset($this->plugins[$pluginName])) {
            return false;
        }

        if (isset($this->activePlugins[$pluginName]) && method_exists($this->activePlugins[$pluginName], 'deactivate')) {
            $this->activePlugins[$pluginName]->deactivate();
        }

        $this->plugins[$pluginName]['active'] = false;
        unset($this->activePlugins[$pluginName]);

        $this->removeActivePluginFromDatabase($pluginName);

        return true;
    }

    private function saveActivePluginToDatabase($pluginName)
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO active_plugins (plugin_name) VALUES (:plugin_name)");
            $stmt->execute([':plugin_name' => $pluginName]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function removeActivePluginFromDatabase($pluginName)
    {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare("DELETE FROM active_plugins WHERE plugin_name = :plugin_name");
            $stmt->execute([':plugin_name' => $pluginName]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    private function getPluginsDirectory(): string
    {
        $possiblePaths = [
            base_path('plugins/'),
            dirname(__DIR__, 2) . '/plugins/'
        ];

        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                return rtrim($path, '/') . '/';
            }
        }

        return base_path('plugins/');
    }

    public function getPlugins()
    {

        foreach ($this->plugins as $name => &$pluginData) {
            // ОБЯЗАТЕЛЬНО обновляем статус из реальных данных
            $pluginData['active'] = $this->isPluginActive($name);
        }
        unset($pluginData); // Разрываем ссылку
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
            return true;
        }

        // Затем проверяем в массиве всех плагинов
        if (isset($this->plugins[$name]['active']) && $this->plugins[$name]['active'] === true) {
            return true;
        }

        // И наконец проверяем в БД
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM active_plugins WHERE plugin_name = :plugin_name");
                $stmt->execute([':plugin_name' => $name]);
                $count = $stmt->fetchColumn();
                $isActive = $count > 0;

                return $isActive;
            } catch (PDOException $e) {
            }
        }

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
            } catch (PDOException $e) {
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