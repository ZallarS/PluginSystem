<?php
declare(strict_types=1);

namespace App\Core\Widgets;

use App\Core\Session\SessionManager;

class WidgetManager
{
    private static $instance;
    private array $widgets = [];
    private SessionManager $session;

    public static function getInstance(SessionManager $session = null): self
    {
        if (self::$instance === null) {
            if ($session === null) {
                // Попробуем получить из контейнера
                try {
                    $session = app(SessionManager::class);
                } catch (\Exception $e) {
                    throw new \RuntimeException('SessionManager is required for WidgetManager');
                }
            }
            self::$instance = new self($session);
        }
        return self::$instance;
    }

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function registerWidget(array $widgetData): bool
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

        // Автоматически показываем новый виджет
        $this->showWidget($widgetId);

        return true;
    }

    public function getWidget(string $widgetId): ?array
    {
        return $this->widgets[$widgetId] ?? null;
    }

    public function getAllWidgets(): array
    {
        return $this->widgets;
    }

    public function getVisibleWidgets(): array
    {
        $visible = [];
        foreach ($this->widgets as $widgetId => $widget) {
            if ($this->isWidgetVisible($widgetId)) {
                $visible[$widgetId] = $widget;
            }
        }
        return $visible;
    }

    public function getHiddenWidgets(): array
    {
        $hidden = [];
        foreach ($this->widgets as $widgetId => $widget) {
            if (!$this->isWidgetVisible($widgetId)) {
                $hidden[] = $this->formatWidgetInfo($widgetId, $widget);
            }
        }
        return $hidden;
    }

    public function isWidgetVisible(string $widgetId): bool
    {
        $userWidgets = $this->session->get('user_widgets', []);
        return isset($userWidgets[$widgetId]) && $userWidgets[$widgetId];
    }

    public function showWidget(string $widgetId): void
    {
        $userWidgets = $this->session->get('user_widgets', []);
        $userWidgets[$widgetId] = true;
        $this->session->set('user_widgets', $userWidgets);
    }

    public function hideWidget(string $widgetId): void
    {
        $userWidgets = $this->session->get('user_widgets', []);
        $userWidgets[$widgetId] = false;
        $this->session->set('user_widgets', $userWidgets);
    }

    public function widgetExists(string $widgetId): bool
    {
        return isset($this->widgets[$widgetId]);
    }

    public function renderWidget(string $widgetId): string
    {
        $widget = $this->getWidget($widgetId);

        if (!$widget || !$this->isWidgetVisible($widgetId)) {
            return '';
        }

        $content = is_callable($widget['content']) ? $widget['content']() : $widget['content'];

        return sprintf(
            '<div class="dashboard-widget widget-%s" data-widget-id="%s">
                <div class="widget-header">
                    <div class="widget-title">
                        <i class="bi %s"></i>
                        <h5>%s</h5>
                    </div>
                    <div class="widget-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary widget-toggle" data-widget-id="%s">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <div class="widget-body">%s</div>
            </div>',
            htmlspecialchars($widget['size']),
            htmlspecialchars($widget['id']),
            htmlspecialchars($widget['icon']),
            htmlspecialchars($widget['title']),
            htmlspecialchars($widget['id']),
            $content
        );
    }

    public function renderWidgetsGrid(): string
    {
        $html = '<div class="dashboard-widgets-grid">';

        foreach ($this->getVisibleWidgets() as $widgetId => $widget) {
            $html .= $this->renderWidget($widgetId);
        }

        $html .= '</div>';
        return $html;
    }

    private function formatWidgetInfo(string $widgetId, array $widget): array
    {
        return [
            'id' => $widgetId,
            'title' => $widget['title'] ?? 'Без названия',
            'icon' => $widget['icon'] ?? 'bi-question-circle',
            'description' => $widget['description'] ?? 'Описание отсутствует',
            'size' => $widget['size'] ?? 'medium',
            'source' => $widget['source'] ?? 'system',
            'plugin_name' => $widget['plugin_name'] ?? null
        ];
    }
}