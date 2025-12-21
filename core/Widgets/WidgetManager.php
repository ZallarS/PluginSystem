<?php

namespace Core\Widgets;

class WidgetManager
{
    private static $instance;
    private $widgets = [];
    private $userWidgets = [];

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->loadUserWidgets();
        $this->registerDefaultWidgets();
    }

    private function loadUserWidgets()
    {
        if (isset($_SESSION['user_widgets'])) {
            $this->userWidgets = $_SESSION['user_widgets'];
        } else {
            $this->userWidgets = [
                'system_stats' => true,
                'recent_activity' => true,
                'quick_links' => true,
                'server_info' => true,
            ];
        }
    }

    private function registerDefaultWidgets()
    {
        $this->widgets = [
            'system_stats' => [
                'id' => 'system_stats',
                'title' => 'Статистика системы',
                'description' => 'Основные показатели системы',
                'icon' => 'bi-speedometer2',
                'size' => 'medium',
                'source' => 'system',
                'content' => function() {
                    return '
                    <div class="row">
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="bi bi-plug"></i>
                                </div>
                                <div class="stat-info">
                                    <h5>0</h5>
                                    <small>Плагинов</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card">
                                <div class="stat-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div class="stat-info">
                                    <h5>1</h5>
                                    <small>Пользователей</small>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
            ],
            'recent_activity' => [
                'id' => 'recent_activity',
                'title' => 'Последние действия',
                'description' => 'История активности',
                'icon' => 'bi-clock-history',
                'size' => 'medium',
                'source' => 'system',
                'content' => function() {
                    return '
                    <ul class="activity-list">
                        <li>
                            <i class="bi bi-check-circle text-success"></i>
                            <span>Вы вошли в систему</span>
                            <small>Только что</small>
                        </li>
                    </ul>';
                }
            ],
            'quick_links' => [
                'id' => 'quick_links',
                'title' => 'Быстрые ссылки',
                'description' => 'Доступ к основным разделам',
                'icon' => 'bi-link-45deg',
                'size' => 'small',
                'content' => function() {
                    return '
                    <div class="quick-links">
                        <a href="/admin/plugins" class="quick-link">
                            <i class="bi bi-plug"></i>
                            <span>Плагины</span>
                        </a>
                        <a href="#" class="quick-link">
                            <i class="bi bi-palette"></i>
                            <span>Темы</span>
                        </a>
                        <a href="#" class="quick-link">
                            <i class="bi bi-gear"></i>
                            <span>Настройки</span>
                        </a>
                    </div>';
                }
            ],
            'server_info' => [
                'id' => 'server_info',
                'title' => 'Информация о сервере',
                'description' => 'Технические данные',
                'icon' => 'bi-server',
                'size' => 'large',
                'content' => function() {
                    return '
                    <div class="server-info">
                        <div class="info-row">
                            <span>PHP версия:</span>
                            <strong>' . phpversion() . '</strong>
                        </div>
                        <div class="info-row">
                            <span>Версия MySQL:</span>
                            <strong>5.7+</strong>
                        </div>
                        <div class="info-row">
                            <span>Память:</span>
                            <strong>' . ini_get('memory_limit') . '</strong>
                        </div>
                    </div>';
                }
            ]
        ];
    }

    public function getWidgets()
    {
        $widgets = [];
        foreach ($this->widgets as $widgetId => $widgetData) {
            if ($this->isWidgetVisible($widgetId)) {
                $widgets[$widgetId] = $widgetData;
            }
        }
        return $widgets;
    }

    public function getAllWidgets()
    {
        return $this->widgets;
    }

    public function toggleWidget($widgetId, $enabled)
    {
        $this->userWidgets[$widgetId] = $enabled;
        $_SESSION['user_widgets'] = $this->userWidgets;
    }

    public function renderWidget($widgetId)
    {
        if (!isset($this->widgets[$widgetId]) || !$this->isWidgetVisible($widgetId)) {
            return '';
        }

        $widget = $this->widgets[$widgetId];
        $content = is_callable($widget['content']) ? $widget['content']() : $widget['content'];

        return '
        <div class="dashboard-widget widget-' . $widget['size'] . '" data-widget-id="' . $widget['id'] . '">
            <div class="widget-header">
                <div class="widget-title">
                    <i class="bi ' . $widget['icon'] . '"></i>
                    <h5>' . $widget['title'] . '</h5>
                </div>
                <div class="widget-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary widget-toggle" 
                            data-widget-id="' . $widget['id'] . '">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>
            <div class="widget-body">
                ' . $content . '
            </div>
        </div>';
    }

    public function renderWidgetsGrid()
    {
        $widgets = $this->getWidgets();
        $html = '<div class="dashboard-widgets-grid">';

        foreach ($widgets as $widgetId => $widgetData) {
            $html .= $this->renderWidget($widgetId);
        }

        $html .= '</div>';
        return $html;
    }

    public function getHiddenWidgets()
    {
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            // Убедимся, что менеджер виджетов существует
            if (!class_exists('\Core\Widgets\WidgetManager')) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'WidgetManager class not found']);
                exit;
            }

            $widgetManager = \Core\Widgets\WidgetManager::getInstance();

            // Проверяем, есть ли метод getAllWidgets
            if (!method_exists($widgetManager, 'getAllWidgets')) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'WidgetManager method getAllWidgets not found']);
                exit;
            }

            $allWidgets = $widgetManager->getAllWidgets();
            $hiddenWidgets = [];

            foreach ($allWidgets as $widgetId => $widgetData) {
                // Проверяем, есть ли метод isWidgetVisible
                if (method_exists($widgetManager, 'isWidgetVisible')) {
                    if (!$widgetManager->isWidgetVisible($widgetId)) {
                        $hiddenWidgets[] = [
                            'id' => $widgetId,
                            'title' => $widgetData['title'] ?? 'Без названия',
                            'icon' => $widgetData['icon'] ?? 'bi-question-circle',
                            'description' => $widgetData['description'] ?? 'Описание отсутствует',
                            'source' => $widgetData['source'] ?? 'system',
                            'plugin_name' => $widgetData['plugin_name'] ?? null
                        ];
                    }
                } else {
                    // Простая проверка через сессию
                    if (!isset($_SESSION['user_widgets'][$widgetId]) || !$_SESSION['user_widgets'][$widgetId]) {
                        $hiddenWidgets[] = [
                            'id' => $widgetId,
                            'title' => $widgetData['title'] ?? 'Без названия',
                            'icon' => $widgetData['icon'] ?? 'bi-question-circle',
                            'description' => $widgetData['description'] ?? 'Описание отсутствует',
                            'source' => $widgetData['source'] ?? 'system',
                            'plugin_name' => $widgetData['plugin_name'] ?? null
                        ];
                    }
                }
            }

            // Всегда возвращаем JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'hidden_widgets' => $hiddenWidgets
            ], JSON_UNESCAPED_UNICODE);
            exit;

        } catch (\Exception $e) {
            error_log("Error in getHiddenWidgets: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    public function isWidgetVisible($widgetId)
    {
        return isset($this->userWidgets[$widgetId]) && $this->userWidgets[$widgetId];
    }

    public function getWidget($widgetId)
    {
        return $this->widgets[$widgetId] ?? null;
    }

    public function widgetExists($widgetId)
    {
        return isset($this->widgets[$widgetId]);
    }

    public function registerWidget(array $widgetData)
    {
        if (!isset($widgetData['id'])) {
            error_log("WidgetManager: Cannot register widget without ID");
            return false;
        }

        $widgetId = $widgetData['id'];

        // Проверяем, не существует ли уже такой виджет
        if ($this->widgetExists($widgetId)) {
            error_log("WidgetManager: Widget '{$widgetId}' already exists, skipping");
            return false; // Или можно обновить существующий виджет
        }

        $requiredFields = ['title', 'description', 'icon', 'size', 'content'];
        foreach ($requiredFields as $field) {
            if (!isset($widgetData[$field])) {
                error_log("WidgetManager: Widget '{$widgetId}' missing required field: {$field}");
                return false;
            }
        }

        // Регистрируем виджет
        $this->widgets[$widgetId] = [
            'id' => $widgetId,
            'title' => $widgetData['title'],
            'description' => $widgetData['description'],
            'icon' => $widgetData['icon'],
            'size' => $widgetData['size'],
            'content' => $widgetData['content'],
            'source' => $widgetData['source'] ?? 'plugin',
            'plugin_name' => $widgetData['plugin_name'] ?? null,
        ];

        // Если пользователь еще не настроил этот виджет, включаем его по умолчанию
        if (!isset($this->userWidgets[$widgetId])) {
            $this->userWidgets[$widgetId] = true;
            $_SESSION['user_widgets'] = $this->userWidgets;
            error_log("WidgetManager: Widget '{$widgetId}' enabled by default");
        }

        error_log("WidgetManager: Widget '{$widgetId}' registered successfully");
        return true;
    }

    public function unregisterWidget($widgetId)
    {
        if (isset($this->widgets[$widgetId])) {
            unset($this->widgets[$widgetId]);

            if (isset($this->userWidgets[$widgetId])) {
                unset($this->userWidgets[$widgetId]);
                $_SESSION['user_widgets'] = $this->userWidgets;
            }

            error_log("WidgetManager: Widget '{$widgetId}' unregistered");
            return true;
        }

        return false;
    }
    public function refreshWidget($widgetId)
    {
        if (!isset($this->widgets[$widgetId])) {
            return null;
        }

        $widget = $this->widgets[$widgetId];

        if (is_callable($widget['content'])) {
            return $widget['content']();
        }

        return $widget['content'];
    }
}