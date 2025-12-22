<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Widgets\WidgetManager;
use App\Core\HookManager;

class WidgetService
{
    private WidgetManager $widgetManager;
    private HookManager $hookManager;
    private ?CacheService $cacheService;

    public function __construct(
        WidgetManager $widgetManager = null,
        HookManager $hookManager = null,
        CacheService $cacheService = null
    ) {
        $this->widgetManager = $widgetManager ?? WidgetManager::getInstance();
        $this->hookManager = $hookManager ?? HookManager::getInstance();
        $this->cacheService = $cacheService;
    }

    /**
     * Получить все видимые виджеты
     */
    public function getVisibleWidgets(): array
    {
        $cacheKey = 'widgets_visible_' . md5(json_encode($_SESSION['user_widgets'] ?? []));

        if ($this->cacheService && $this->cacheService->has($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }

        $widgets = $this->widgetManager->getWidgets();
        $widgets = $this->hookManager->applyFilter('widgets_prepare', $widgets);

        if ($this->cacheService) {
            $this->cacheService->put($cacheKey, $widgets, 300); // 5 минут
        }

        return $widgets;
    }

    /**
     * Рендеринг сетки виджетов
     */
    public function renderWidgetsGrid(): string
    {
        $cacheKey = 'widgets_grid_' . md5(json_encode($_SESSION['user_widgets'] ?? []));

        if ($this->cacheService && $this->cacheService->has($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }

        $this->hookManager->doAction('before_widgets_render');

        $html = $this->widgetManager->renderWidgetsGrid();

        $this->hookManager->doAction('after_widgets_render', $html);

        if ($this->cacheService) {
            $this->cacheService->put($cacheKey, $html, 300);
        }

        return $html;
    }

    /**
     * Рендеринг конкретного виджета
     */
    public function renderWidget(string $widgetId): string
    {
        if (!$this->widgetManager->widgetExists($widgetId)) {
            return '';
        }

        // Проверяем видимость виджета
        if (!$this->widgetManager->isWidgetVisible($widgetId)) {
            return '';
        }

        // Действие перед рендерингом виджета
        $this->hookManager->doAction('before_widget_render', $widgetId);

        $html = $this->widgetManager->renderWidget($widgetId);

        // Действие после рендеринга виджета
        $this->hookManager->doAction('after_widget_render', $widgetId, $html);

        return $html;
    }

    /**
     * Регистрация нового виджета
     */
    public function registerWidget(array $widgetData): bool
    {
        // Фильтр для модификации данных виджета перед регистрацией
        $widgetData = $this->hookManager->applyFilter('widget_before_register', $widgetData);

        $result = $this->widgetManager->registerWidget($widgetData);

        if ($result) {
            $this->hookManager->doAction('widget_registered', $widgetData['id']);
        }

        return $result;
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

        // Фильтр для модификации информации о виджете
        return $this->hookManager->applyFilter('widget_info', $widget, $widgetId);
    }

    /**
     * Переключение видимости виджета
     */
    public function toggleWidget(string $widgetId, bool $enabled): bool
    {
        // Действие перед переключением
        $this->hookManager->doAction('before_widget_toggle', $widgetId, $enabled);

        $this->widgetManager->toggleWidget($widgetId, $enabled);

        // Действие после переключения
        $this->hookManager->doAction('after_widget_toggle', $widgetId, $enabled);

        return true;
    }

    /**
     * Получить скрытые виджеты
     */
    public function getHiddenWidgets(): array
    {
        $allWidgets = $this->widgetManager->getAllWidgets();
        $hiddenWidgets = [];

        foreach ($allWidgets as $widgetId => $widgetData) {
            if (!$this->widgetManager->isWidgetVisible($widgetId)) {
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

        return $hiddenWidgets;
    }
}