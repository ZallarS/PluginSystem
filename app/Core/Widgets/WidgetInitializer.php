<?php
declare(strict_types=1);

namespace App\Core\Widgets;

class WidgetInitializer
{
    public static function initializeDefaultWidgets(WidgetManager $widgetManager, array $defaultWidgetIds)
    {
        $userWidgets = $_SESSION['user_widgets'] ?? [];
        $updated = false;

        foreach ($defaultWidgetIds as $widgetId) {
            if (!isset($userWidgets[$widgetId])) {
                $userWidgets[$widgetId] = true;
                $updated = true;
            }
        }

        if ($updated) {
            $_SESSION['user_widgets'] = $userWidgets;
        }
    }
}