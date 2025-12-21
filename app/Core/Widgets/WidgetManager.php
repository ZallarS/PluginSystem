<?php
declare(strict_types=1);

namespace App\Core\Widgets;

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
            return false;
        }

        $widgetId = $widgetData['id'];

        if ($this->widgetExists($widgetId)) {
            return false;
        }

        $requiredFields = ['title', 'description', 'icon', 'size', 'content'];
        foreach ($requiredFields as $field) {
            if (!isset($widgetData[$field])) {
                return false;
            }
        }

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

        if (!isset($this->userWidgets[$widgetId])) {
            $this->userWidgets[$widgetId] = true;
            $_SESSION['user_widgets'] = $this->userWidgets;
        }

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