<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class AdminController extends Controller
{
    private ?WidgetRepository $widgetRepository = null;

    public function dashboard()
    {
        // Проверка аутентификации теперь в middleware - удаляем вызов requireLogin()
        $widgetManager = \App\Core\Widgets\WidgetManager::getInstance();
        $widgetsGrid = $widgetManager->renderWidgetsGrid();

        $data = [
            'title' => 'Панель администратора',
            'message' => 'Добро пожаловать в панель управления! Здесь вы можете управлять плагинами, виджетами и настройками системы.',
            'widgetsGrid' => $widgetsGrid
        ];

        return $this->view('admin.dashboard', $data);
    }

    public function saveWidgets()
    {
        // Проверка аутентификации и CSRF теперь в middleware - удаляем вызовы requireApiAuth() и validateCsrfToken()
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $widgets = json_decode($_POST['widgets'] ?? '{}', true);

            // Базовая валидация
            if (!is_array($widgets)) {
                return $this->json(['error' => 'Invalid widgets data'], 400);
            }

            $_SESSION['user_widgets'] = $widgets;

            return $this->json(['success' => true, 'message' => 'Настройки виджетов сохранены']);
        }

        return $this->json(['error' => 'Invalid request'], 400);
    }

    public function toggleWidget()
    {
        // Проверка аутентификации и CSRF теперь в middleware - удаляем вызовы requireApiAuth() и validateCsrfToken()
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $widgetId = $_POST['widget_id'] ?? '';
            $action = $_POST['action'] ?? '';

            if (!$widgetId) {
                return $this->json(['error' => 'Widget ID is required'], 400);
            }

            $widgetManager = \App\Core\Widgets\WidgetManager::getInstance();

            if ($action === 'hide_widget') {
                $widgetManager->toggleWidget($widgetId, false);
                return $this->json(['success' => true, 'message' => 'Виджет скрыт']);
            } elseif ($action === 'show_widget') {
                $widgetManager->toggleWidget($widgetId, true);
                $html = $widgetManager->renderWidget($widgetId);

                return $this->json([
                    'success' => true,
                    'message' => 'Виджет восстановлен',
                    'html' => $html
                ]);
            }

            return $this->json(['error' => 'Invalid action'], 400);
        }

        return $this->json(['error' => 'Invalid request'], 400);
    }

    public function getHiddenWidgets()
    {
        try {
            $hiddenWidgets = $this->getWidgetRepository()->getHidden();

            return $this->json([
                'success' => true,
                'hidden_widgets' => $hiddenWidgets
            ]);
        } catch (\Exception $e) {
            error_log("Error in getHiddenWidgets: " . $e->getMessage());
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

    public function getWidgetInfo($widgetId)
    {
        $widget = $this->getWidgetRepository()->getWidgetInfo($widgetId);

        if ($widget) {
            return $this->json([
                'success' => true,
                'widget' => $widget
            ]);
        }

        return $this->json(['error' => 'Widget not found'], 404);
    }

    public function getWidgetHtml($widgetId)
    {
        // Проверка аутентификации теперь в middleware - удаляем вызов requireApiAuth()
        $widgetManager = \App\Core\Widgets\WidgetManager::getInstance();
        $widget = $widgetManager->getWidget($widgetId);

        if (!$widget) {
            return $this->json(['error' => 'Widget not found'], 404);
        }

        $html = $widgetManager->renderWidget($widgetId);

        return $this->json([
            'success' => true,
            'html' => $html,
            'widget' => [
                'id' => $widgetId,
                'title' => $widget['title'] ?? '',
                'icon' => $widget['icon'] ?? '',
                'size' => $widget['size'] ?? 'medium'
            ]
        ]);
    }
}