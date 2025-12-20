<?php

namespace Plugins\SimpleTestPlugin;

class Plugin
{
    private $name = 'SimpleTestPlugin';
    private $version = '1.0.0';
    private $author = 'System Administrator';
    private $description = 'Простой тестовый плагин для демонстрации работы плагинов';

    public function activate()
    {
        error_log("Плагин {$this->name} активирован");
        return true;
    }

    public function deactivate()
    {
        error_log("Плагин {$this->name} деактивирован");
        return true;
    }

    public function init()
    {
        error_log("Плагин {$this->name} инициализирован");

        $this->registerHooks();

        return true;
    }

    public function registerRoutes($router)
    {
        error_log("Плагин {$this->name}: регистрация маршрутов");

        // Добавляем маршруты плагина
        $router->get('/plugin-test', function() {
            echo "<h1>Тестовый плагин работает!</h1>";
            echo "<p>Это демонстрация работы плагина SimpleTestPlugin.</p>";
            echo "<p><a href='/'>Вернуться на главную</a></p>";
            exit;
        });

        $router->get('/admin/plugin-test', function() {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }

            echo "<h1>Админ-панель плагина</h1>";
            echo "<p>Это админ-страница тестового плагина.</p>";
            echo "<p><a href='/admin'>Вернуться в админку</a></p>";
            exit;
        });

        error_log("Плагин {$this->name}: маршруты зарегистрированы");
    }

    private function registerHooks()
    {
        // Регистрация хуков для дашборда

        // 1. Хук вверху дашборда
        add_action('dashboard_top', function() {
            echo <<<HTML
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong>SimpleTestPlugin активен!</strong> Этот плагин добавляет новые возможности в систему.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            HTML;
        });

        // 2. Хук для статистики (добавляем новую карточку)
        add_action('dashboard_stats', function() {
            echo <<<HTML
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Тестовый плагин</h6>
                                <h4 class="mb-0">v{$this->version}</h4>
                            </div>
                            <i class="bi bi-plugin display-4 opacity-50"></i>
                        </div>
                        <div class="mt-3">
                            <small><i class="bi bi-person"></i> Автор: {$this->author}</small>
                        </div>
                    </div>
                </div>
            </div>
            HTML;
        });

        // 3. Хук для последних действий
        add_action('dashboard_recent_activity', function() {
            echo <<<HTML
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-plug text-warning me-2"></i>
                    Плагин <strong>{$this->name}</strong> загружен
                </span>
                <small class="text-muted">Только что</small>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-code-slash text-info me-2"></i>
                    Добавлены хуки на дашборд
                </span>
                <small class="text-muted">Только что</small>
            </li>
            HTML;
        });

        // 4. Хук для быстрых действий
        add_action('dashboard_actions', function() {
            echo <<<HTML
            <a href="/plugin-test" class="btn btn-outline-success">
                <i class="bi bi-rocket-takeoff"></i> Тест плагина
            </a>
            <a href="/admin/plugin-test" class="btn btn-outline-info">
                <i class="bi bi-gear"></i> Настройки плагина
            </a>
            HTML;
        });

        // 5. Хук внизу дашборда
        add_action('dashboard_bottom', function() {
            echo <<<HTML
            <div class="card mt-4">
                <div class="card-header">
                    <i class="bi bi-puzzle me-2"></i> Информация от SimpleTestPlugin
                </div>
                <div class="card-body">
                    <h5 class="card-title">{$this->name}</h5>
                    <p class="card-text">{$this->description}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li><strong>Версия:</strong> {$this->version}</li>
                                <li><strong>Автор:</strong> {$this->author}</li>
                                <li><strong>Статус:</strong> <span class="badge bg-success">Активен</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <small>
                                    <i class="bi bi-info-circle"></i>
                                    Этот контент добавлен через систему хуков плагина SimpleTestPlugin.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            HTML;
        });

        // 6. Фильтр для заголовка дашборда (пример)
        add_filter('dashboard_title', function($title) {
            return $title . ' | SimpleTestPlugin активен';
        });

        // 7. Хук в сайдбаре (если есть)
        if (has_action('dashboard_sidebar')) {
            add_action('dashboard_sidebar', function() {
                echo <<<HTML
                <div class="card mt-3">
                    <div class="card-header">
                        <i class="bi bi-puzzle me-2"></i> Плагины
                    </div>
                    <div class="card-body">
                        <p class="small">Активный плагин: <strong>{$this->name}</strong></p>
                        <a href="/admin/plugins" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-gear"></i> Управление плагинами
                        </a>
                    </div>
                </div>
                HTML;
            });
        }

        error_log("Плагин {$this->name}: хуки зарегистрированы");
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDescription()
    {
        return $this->description;
    }
}