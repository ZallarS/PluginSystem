<?php

    declare(strict_types=1);

    namespace App\Core\Widgets;

    class WidgetRenderer implements WidgetRendererInterface
    {
        public function render(string $widgetId, array $widget): string
        {
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