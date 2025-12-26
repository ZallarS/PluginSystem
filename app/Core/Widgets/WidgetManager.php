<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    class WidgetManager
    {
        private array $widgets = [];
        private WidgetStorageInterface $storage;
        private WidgetRendererInterface $renderer;

        public function __construct(
            WidgetStorageInterface $storage,
            WidgetRendererInterface $renderer
        ) {
            $this->storage = $storage;
            $this->renderer = $renderer;
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
            return $this->storage->getWidgetState($widgetId);
        }

        public function showWidget(string $widgetId): void
        {
            $this->storage->setWidgetState($widgetId, true);
        }

        public function hideWidget(string $widgetId): void
        {
            $this->storage->setWidgetState($widgetId, false);
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

            return $this->renderer->render($widgetId, $widget);
        }

        public function renderWidgetsGrid(): string
        {
            return $this->renderer->renderGrid($this->getVisibleWidgets());
        }

        /**
         * Статический метод для обратной совместимости с плагинами
         * @deprecated Используйте DI контейнер вместо этого
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