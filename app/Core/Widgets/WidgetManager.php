<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    /**
     * WidgetManager class
     *
     * Manages the registration, visibility, and rendering of widgets.
     * Acts as a central hub for widget functionality in the system.
     *
     * @package App\Core\Widgets
     */
    class WidgetManager
    {
        /**
         * @var array The registered widgets
         */
        private array $widgets = [];

        /**
         * @var WidgetStorageInterface The storage implementation for widget states
         */
        private WidgetStorageInterface $storage;

        /**
         * @var WidgetRendererInterface The renderer implementation for widgets
         */
        private WidgetRendererInterface $renderer;


        /**
         * Create a new widget manager instance.
         *
         * @param WidgetStorageInterface $storage The storage implementation
         * @param WidgetRendererInterface $renderer The renderer implementation
         */
        public function __construct(
            WidgetStorageInterface $storage,
            WidgetRendererInterface $renderer
        ) {
            $this->storage = $storage;
            $this->renderer = $renderer;
        }

        /**
         * Register a new widget.
         *
         * @param array $widgetData The widget configuration
         * @return bool True if registration was successful
         */
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

            return true;
        }

        /**
         * Get a specific widget by ID.
         *
         * @param string $widgetId The widget identifier
         * @return array|null The widget configuration or null if not found
         */
        public function getWidget(string $widgetId): ?array
        {
            return $this->widgets[$widgetId] ?? null;
        }

        /**
         * Get all registered widgets.
         *
         * @return array The widgets array
         */
        public function getAllWidgets(): array
        {
            return $this->widgets;
        }

        /**
         * Get all visible widgets.
         *
         * @return array The visible widgets
         */
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

        /**
         * Get information about all hidden widgets.
         *
         * @return array The hidden widgets with their information
         */
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

        /**
         * Check if a widget is currently visible.
         *
         * @param string $widgetId The widget identifier
         * @return bool True if the widget is visible
         */
        public function isWidgetVisible(string $widgetId): bool
        {
            return $this->storage->getWidgetState($widgetId);
        }

        /**
         * Make a widget visible.
         *
         * @param string $widgetId The widget identifier
         * @return void
         */
        public function showWidget(string $widgetId): void
        {
            $this->storage->setWidgetState($widgetId, true);
        }

        /**
         * Hide a widget.
         *
         * @param string $widgetId The widget identifier
         * @return void
         */
        public function hideWidget(string $widgetId): void
        {
            $this->storage->setWidgetState($widgetId, false);
        }

        /**
         * Check if a widget exists.
         *
         * @param string $widgetId The widget identifier
         * @return bool True if the widget exists
         */
        public function widgetExists(string $widgetId): bool
        {
            return isset($this->widgets[$widgetId]);
        }

        /**
         * Render a single widget as HTML.
         *
         * @param string $widgetId The widget identifier
         * @return string The rendered HTML or empty string if widget not found/invisible
         */
        public function renderWidget(string $widgetId): string
        {
            $widget = $this->getWidget($widgetId);

            if (!$widget || !$this->isWidgetVisible($widgetId)) {
                return '';
            }

            return $this->renderer->render($widgetId, $widget);
        }

        /**
         * Render a grid of all visible widgets as HTML.
         *
         * @return string The rendered HTML grid
         */
        public function renderWidgetsGrid(): string
        {
            return $this->renderer->renderGrid($this->getVisibleWidgets());
        }

        /**
         * Get an instance of the widget manager.
         *
         * Static method for backward compatibility with plugins.
         * Uses dependency injection when available, falls back to direct instantiation.
         *
         * @deprecated Use DI container instead of this method
         * @param mixed $session The session instance (for fallback)
         * @return self The widget manager instance
         */
        public static function getInstance($session = null): self
        {
            // Получаем экземпляр через контейнер
            $widgetManager = app(self::class);

            if (!$widgetManager) {
                // Fallback для плагинов, которые могут вызывать этот метод до инициализации приложения
                $storage = new SessionWidgetStorage($session);
                $renderer = new WidgetRenderer();
                $widgetManager = new self($storage, $renderer);
            }

            return $widgetManager;
        }

        /**
         * Format widget information for the hidden widgets list.
         *
         * @param string $widgetId The widget identifier
         * @param array $widget The widget configuration
         * @return array The formatted widget information
         */
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