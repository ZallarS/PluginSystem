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
        // Убираем registerDefaultWidgets - виджеты будут регистрироваться через плагин
    }

    private function loadUserWidgets()
    {
        if (isset($_SESSION['user_widgets'])) {
            $this->userWidgets = $_SESSION['user_widgets'];
        } else {
            // Инициализируем пустым массивом
            $this->userWidgets = [];
            $_SESSION['user_widgets'] = $this->userWidgets;
        }
    }

    public function getWidgets()
    {
        $visibleWidgets = [];
        foreach ($this->widgets as $widgetId => $widgetData) {
            if ($this->isWidgetVisible($widgetId)) {
                $visibleWidgets[$widgetId] = $widgetData;
            }
        }
        return $visibleWidgets;
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

        // Добавляем виджет в user_widgets если его там нет
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