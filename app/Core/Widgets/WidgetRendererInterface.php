<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    interface WidgetRendererInterface
    {
        /**
         * Отрендерить виджет
         */
        public function render(string $widgetId, array $widget): string;

        /**
         * Отрендерить сетку виджетов
         */
        public function renderGrid(array $widgets): string;
    }