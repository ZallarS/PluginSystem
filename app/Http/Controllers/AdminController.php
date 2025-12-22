<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!$this->requireLogin()) {
            return;
        }

        $data = [
            'title' => 'Панель администратора',
            'message' => 'Добро пожаловать в панель управления! Здесь вы можете управлять плагинами, виджетами и настройками системы.'
        ];

        echo $this->view('admin.dashboard', $data);
    }

    public function saveWidgets()
    {
        // Проверяем авторизацию
        if (!$this->requireApiAuth()) {
            return;
        }

        // Проверяем CSRF
        if (!$this->validateCsrfToken()) {
            return;
        }

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
        // Проверяем авторизацию
        if (!$this->requireApiAuth()) {
            return;
        }

        // Проверяем CSRF
        if (!$this->validateCsrfToken()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $widgetId = $_POST['widget_id'] ?? '';
            $action = $_POST['action'] ?? '';

            if (!$widgetId) {
                return $this->json(['error' => 'Widget ID is required'], 400);
            }

            $widgetService = $this->getWidgetService();

            if ($action === 'hide_widget') {
                $widgetService->toggleWidget($widgetId, false);
                return $this->json(['success' => true, 'message' => 'Виджет скрыт']);
            } elseif ($action === 'show_widget') {
                $widgetService->toggleWidget($widgetId, true);
                $html = $widgetService->renderWidget($widgetId);

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

    private function getWidgetService()
    {
        try {
            $app = \App\Core\Application::getInstance();
            if ($app && $app->getContainer()->has(\App\Services\WidgetService::class)) {
                return $app->getContainer()->get(\App\Services\WidgetService::class);
            }
        } catch (\Exception $e) {
            error_log("Error getting WidgetService: " . $e->getMessage());
        }

        // Fallback
        return new \App\Services\WidgetService();
    }

    public function getHiddenWidgets()
    {
        // Проверяем авторизацию
        if (!$this->requireApiAuth()) {
            return;
        }

        try {
            $widgetService = $this->getWidgetService();
            $hiddenWidgets = $widgetService->getHiddenWidgets();

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
        // Проверяем авторизацию
        if (!$this->requireApiAuth()) {
            return;
        }

        $widgetManager = \App\Core\Widgets\WidgetManager::getInstance();
        $widget = $widgetManager->getWidget($widgetId);

        if ($widget) {
            return $this->json([
                'success' => true,
                'widget' => [
                    'id' => $widgetId,
                    'title' => $widget['title'] ?? '',
                    'description' => $widget['description'] ?? '',
                    'icon' => $widget['icon'] ?? '',
                    'size' => $widget['size'] ?? 'medium'
                ]
            ]);
        }

        return $this->json(['error' => 'Widget not found'], 404);
    }

    public function getWidgetHtml($widgetId)
    {
        // Проверяем авторизацию
        if (!$this->requireApiAuth()) {
            return;
        }

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