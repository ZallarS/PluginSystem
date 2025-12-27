<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    /**
     * WidgetRenderer class
     *
     * Renders widgets and widget grids as HTML.
     * Implements the WidgetRendererInterface contract.
     *
     * @package App\Core\Widgets
     */
    class WidgetRenderer implements WidgetRendererInterface
    {
        /**
         * Render a single widget as HTML.
         *
         * @param string $widgetId The widget identifier
         * @param array $widget The widget configuration
         * @return string The rendered HTML
         */
        public function render(string $widgetId, array $widget): string
        {
            // Execute callable content or use raw content
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

        /**
         * Render a grid of widgets as HTML.
         *
         * @param array $widgets The widgets to render
         * @return string The rendered HTML grid
         */
        public function renderGrid(array $widgets): string
        {
            $html = '<div class="dashboard-widgets-grid">';

            foreach ($widgets as $widgetId => $widget) {
                $html .= $this->render($widgetId, $widget);
            }

            $html .= '</div>';
            return $html;
        }
    }