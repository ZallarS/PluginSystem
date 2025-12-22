<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Widgets\WidgetManager;

class WidgetRepository
{
    private WidgetManager $widgetManager;

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    /**
     * Получить все виджеты
     */
    public function getAll(): array
    {
        return $this->widgetManager->getAllWidgets();
    }

    /**
     * Получить видимые виджеты
     */
    public function getVisible(): array
    {
        $allWidgets = $this->getAll();
        $visibleWidgets = [];

        foreach ($allWidgets as $widgetId => $widgetData) {
            if ($this->widgetManager->isWidgetVisible($widgetId)) {
                $visibleWidgets[$widgetId] = $widgetData;
            }
        }

        return $visibleWidgets;
    }

    /**
     * Получить скрытые виджеты
     */
    public function getHidden(): array
    {
        $allWidgets = $this->getAll();
        $hiddenWidgets = [];

        foreach ($allWidgets as $widgetId => $widgetData) {
            if (!$this->widgetManager->isWidgetVisible($widgetId)) {
                $hiddenWidgets[] = $this->formatWidgetInfo($widgetId, $widgetData);
            }
        }

        return $hiddenWidgets;
    }

    /**
     * Получить информацию о виджете
     */
    public function getWidgetInfo(string $widgetId): ?array
    {
        $widget = $this->widgetManager->getWidget($widgetId);

        if (!$widget) {
            return null;
        }

        return $this->formatWidgetInfo($widgetId, $widget);
    }

    /**
     * Форматирование информации о виджете
     */
    private function formatWidgetInfo(string $widgetId, array $widgetData): array
    {
        return [
            'id' => $widgetId,
            'title' => $widgetData['title'] ?? 'Без названия',
            'icon' => $widgetData['icon'] ?? 'bi-question-circle',
            'description' => $widgetData['description'] ?? 'Описание отсутствует',
            'size' => $widgetData['size'] ?? 'medium',
            'source' => $widgetData['source'] ?? 'system',
            'plugin_name' => $widgetData['plugin_name'] ?? null
        ];
    }
}