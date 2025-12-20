<?php

namespace TestPlugin;

class Plugin
{
    private $config;
    private $settings;

    public function __construct()
    {
        // Загружаем конфигурацию плагина
        $configFile = __DIR__ . '/plugin.json';
        if (file_exists($configFile)) {
            $this->config = json_decode(file_get_contents($configFile), true);
        } else {
            $this->config = [];
        }

        // Загружаем настройки плагина
        $this->loadSettings();
    }

    public function init()
    {
        error_log("TestPlugin: Инициализация плагина");

        // Регистрируем хуки
        $this->registerHooks();

        // Добавляем пункт в меню админки
        $this->addAdminMenu();

        // Регистрируем маршруты
        $this->registerRoutes();
    }

    private function loadSettings()
    {
        // Здесь можно загружать настройки из базы данных или файла
        $this->settings = [
            'enable_feature' => true,
            'max_items' => 10,
            'show_welcome' => true,
            'custom_message' => 'Добро пожаловать в тестовый плагин!'
        ];
    }

    private function registerHooks()
    {
        // Регистрация хуков
        // В реальном плагине здесь была бы регистрация в системе хуков
        error_log("TestPlugin: Регистрация хуков");
    }

    private function addAdminMenu()
    {
        // Добавляем пункт в меню админки
        // В реальной системе это бы делалось через API системы
        error_log("TestPlugin: Добавление пункта в меню админки");

        // В реальном плагине здесь будет добавление пункта в меню админки
        // через хук или прямое обращение к менеджеру меню
    }

    public function registerRoutes($router = null)
    {
        // Этот метод будет вызван системой при инициализации плагина
        if ($router) {
            // Регистрируем маршруты через предоставленный роутер
            $this->registerRoutesWithRouter($router);
        } else {
            // Альтернативный способ регистрации маршрутов
            $this->registerRoutesDirectly();
        }
    }

    private function registerRoutesWithRouter($router)
    {
        error_log("TestPlugin: Регистрация маршрутов через роутер");

        // Регистрируем маршруты
        $router->get('/test-plugin', [$this, 'showPage']);
        $router->get('/test-plugin/api', [$this, 'apiTest']);
        $router->post('/test-plugin/save', [$this, 'saveData']);
        $router->get('/admin/test-plugin', [$this, 'showAdminPage']);
    }

    private function registerRoutesDirectly()
    {
        // Альтернативный способ регистрации маршрутов
        // через глобальный объект приложения
        if (class_exists('Core\\Core')) {
            $app = \Core\Core::getInstance();
            if ($app) {
                $router = $app->getRouter();
                if ($router) {
                    $this->registerRoutesWithRouter($router);
                }
            }
        }
    }

    // ==================== МЕТОДЫ КОНТРОЛЛЕРА ====================

    /**
     * Отображение основной страницы плагина
     */
    public function showPage()
    {
        $data = [
            'title' => 'Тестовый плагин',
            'message' => 'Добро пожаловать на демонстрационную страницу плагина!',
            'features' => [
                'Модульная архитектура',
                'Поддержка маршрутов',
                'Интеграция с MVC системой',
                'Настройки плагина',
                'Административный интерфейс'
            ],
            'config' => $this->config,
            'settings' => $this->settings,
            'plugin_info' => [
                'name' => $this->config['name'] ?? 'TestPlugin',
                'version' => $this->config['version'] ?? '1.0.0',
                'author' => $this->config['author'] ?? 'Unknown'
            ]
        ];

        $this->renderView('test-plugin.php', $data);
    }

    /**
     * Тестовый API метод
     */
    public function apiTest()
    {
        $data = [
            'status' => 'success',
            'message' => 'Тестовый API работает корректно!',
            'data' => [
                'plugin_name' => $this->config['name'] ?? 'TestPlugin',
                'version' => $this->config['version'] ?? '1.0.0',
                'timestamp' => date('Y-m-d H:i:s'),
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
                ]
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Сохранение данных из формы
     */
    public function saveData()
    {
        // Проверяем метод запроса
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Метод не разрешен";
            exit;
        }

        // Получаем данные из POST
        $name = $_POST['name'] ?? 'Не указано';
        $email = $_POST['email'] ?? 'Не указано';
        $message = $_POST['message'] ?? 'Не указано';

        // Здесь обычно была бы обработка и сохранение данных
        // Для демонстрации просто возвращаем JSON

        $response = [
            'status' => 'success',
            'message' => 'Данные успешно получены!',
            'received_data' => [
                'name' => htmlspecialchars($name),
                'email' => htmlspecialchars($email),
                'message' => htmlspecialchars($message),
                'received_at' => date('Y-m-d H:i:s')
            ]
        ];

        // Устанавливаем flash-сообщение
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_message'] = 'Данные успешно сохранены через плагин!';

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Страница администрирования плагина
     */
    public function showAdminPage()
    {
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
            header('Location: /login');
            exit;
        }

        $data = [
            'title' => 'Управление тестовым плагином',
            'config' => $this->config,
            'settings' => $this->settings,
            'system_info' => [
                'php_version' => PHP_VERSION,
                'plugin_path' => __DIR__,
                'plugin_active' => true,
                'loaded_extensions' => get_loaded_extensions()
            ],
            'hooks_registered' => $this->config['hooks'] ?? [],
            'routes_registered' => $this->config['routes'] ?? []
        ];

        $this->renderView('admin/test-plugin-admin.php', $data);
    }

    /**
     * Вспомогательный метод для рендеринга представлений
     */
    private function renderView($template, $data = [])
    {
        extract($data);

        // Пытаемся найти шаблон в директории плагина
        $pluginViewPath = __DIR__ . '/views/' . $template;

        if (file_exists($pluginViewPath)) {
            // Используем шаблон плагина
            include $pluginViewPath;
        } else {
            // Используем простой вывод если шаблон не найден
            $this->renderDefaultView($template, $data);
        }

        exit;
    }

    /**
     * Рендеринг по умолчанию если шаблон не найден
     */
    private function renderDefaultView($template, $data)
    {
        extract($data);

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$title} | Test Plugin</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
        </head>
        <body>
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container">
                    <a class="navbar-brand" href="/test-plugin">
                        <i class="bi bi-plugin"></i> Тестовый плагин
                    </a>
                </div>
            </nav>
            
            <div class="container mt-4">
                <h1><i class="bi bi-plugin"></i> {$title}</h1>
                <div class="alert alert-info">
                    <p>{$message}</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> Информация о плагине
                            </div>
                            <div class="card-body">
                                <h5>Название: {$plugin_info['name']}</h5>
                                <p>Версия: {$plugin_info['version']}</p>
                                <p>Автор: {$plugin_info['author']}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-gear"></i> Настройки плагина
                            </div>
                            <div class="card-body">
                                <p>Функционал включен: {$settings['enable_feature'] ? 'Да' : 'Нет'}</p>
                                <p>Максимум элементов: {$settings['max_items']}</p>
                                <p>Показывать приветствие: {$settings['show_welcome'] ? 'Да' : 'Нет'}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h3>Возможности плагина:</h3>
                    <ul class="list-group">
        HTML;

        foreach ($features as $feature) {
            echo "<li class='list-group-item'><i class='bi bi-check-circle text-success me-2'></i>{$feature}</li>";
        }

        echo <<<HTML
                    </ul>
                </div>
                
                <div class="mt-4">
                    <h3>Тестирование форм:</h3>
                    <form method="POST" action="/test-plugin/save" class="mt-3">
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Сообщение</label>
                            <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Отправить данные
                        </button>
                    </form>
                </div>
                
                <div class="mt-4">
                    <a href="/test-plugin/api" class="btn btn-outline-info">
                        <i class="bi bi-code-slash"></i> Тест API
                    </a>
                    
                    <a href="/admin" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-speedometer2"></i> В админку
                    </a>
                    
                    <a href="/" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-house"></i> На главную
                    </a>
                </div>
            </div>
            
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        HTML;
    }

    // ==================== СЕРВИСНЫЕ МЕТОДЫ ====================

    /**
     * Получить конфигурацию плагина
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Получить настройки плагина
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Обновить настройки плагина
     */
    public function updateSettings($newSettings)
    {
        $this->settings = array_merge($this->settings, $newSettings);
        // Здесь обычно была бы запись настроек в базу данных или файл
        return true;
    }

    /**
     * Метод для деактивации плагина
     */
    public function deactivate()
    {
        error_log("TestPlugin: Деактивация плагина");
        // Очистка временных данных, отписка от хуков и т.д.
        return true;
    }

    /**
     * Метод для активации плагина
     */
    public function activate()
    {
        error_log("TestPlugin: Активация плагина");
        // Инициализация данных, регистрация хуков и т.д.
        return true;
    }
}