<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Widgets\WidgetManager;
use App\Core\HookManager;

class WidgetService
{
    private WidgetRepository $widgetRepository;
    private WidgetManager $widgetManager;
    private HookManager $hookManager;
    private ?CacheService $cacheService;

    public function __construct(?string $cachePath = null, ?int $defaultTtl = null)
    {
        // Получаем настройки из конфига
        $config = config('widgets.cache', []);

        $this->cachePath = $cachePath ?? $config['path'] ?? storage_path('cache');
        $this->defaultTtl = $defaultTtl ?? $config['ttl'] ?? 3600;

        // Создаем директорию если не существует
        if (!file_exists($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Получить все видимые виджеты
     */
    public function getVisibleWidgets(): array
    {
        $cacheKey = 'widgets_visible_' . md5(json_encode($_SESSION['user_widgets'] ?? []));

        // Проверяем конфигурацию
        $cacheEnabled = config('widgets.cache.enabled', true);

        if ($cacheEnabled && $this->cacheService && $this->cacheService->has($cacheKey)) {
            return $this->cacheService->get($cacheKey);
        }

        $widgets = $this->widgetManager->getWidgets();
        $widgets = $this->hookManager->applyFilter('widgets_prepare', $widgets);

        if ($cacheEnabled && $this->cacheService) {
            $cacheTtl = config('widgets.cache.ttl', 300);
            $this->cacheService->put($cacheKey, $widgets, $cacheTtl);
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
        $widget = $this->widgetRepository->getWidgetInfo($widgetId);

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
        return $this->widgetRepository->getHidden();
    }

}