<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    /**
     * WidgetRendererInterface interface
     *
     * Defines the contract for rendering widgets and widget grids.
     * Allows different rendering strategies to be implemented.
     *
     * @package App\Core\Widgets
     */
    interface WidgetRendererInterface
    {
        /**
         * Render a single widget.
         *
         * @param string $widgetId The widget identifier
         * @param array $widget The widget configuration
         * @return string The rendered HTML
         */
        public function render(string $widgetId, array $widget): string;

        /**
         * Render a grid of widgets.
         *
         * @param array $widgets The widgets to render
         * @return string The rendered HTML grid
         */
        public function renderGrid(array $widgets): string;
    }