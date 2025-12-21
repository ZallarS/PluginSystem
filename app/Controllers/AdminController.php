<?php

namespace App\Controllers;

class AdminController extends BaseController
{
    public function dashboard()
    {
        // ВРЕМЕННО ОТКЛЮЧАЕМ ПРОВЕРКУ
        // if (!isset($_SESSION['user_id'])) {
        //     $this->redirect('/login');
        // }

        $data = [
            'title' => 'Панель администратора',
            'message' => 'Добро пожаловать в панель управления!'
        ];

        echo $this->view('admin.dashboard', $data);
    }

    // === ДОБАВЛЯЕМ НОВЫЕ МЕТОДЫ ===

    public function saveWidgets()
    {
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $widgets = json_decode($_POST['widgets'] ?? '{}', true);
            $_SESSION['user_widgets'] = $widgets;

            return $this->json(['success' => true, 'message' => 'Настройки виджетов сохранены']);
        }

        return $this->json(['error' => 'Invalid request'], 400);
    }

    public function toggleWidget()
    {
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $widgetId = $_POST['widget_id'] ?? '';
            $action = $_POST['action'] ?? '';

            if (!$widgetId) {
                return $this->json(['error' => 'Widget ID is required'], 400);
            }

            $widgetManager = \Core\Widgets\WidgetManager::getInstance();

            if ($action === 'hide_widget') {
                $widgetManager->toggleWidget($widgetId, false);
                return $this->json(['success' => true, 'message' => 'Виджет скрыт']);
            } elseif ($action === 'show_widget') {
                $widgetManager->toggleWidget($widgetId, true);

                // Также возвращаем HTML виджета для мгновенного отображения
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
        // Проверяем авторизацию
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Получаем менеджер виджетов
            $widgetManager = \Core\Widgets\WidgetManager::getInstance();

            // Получаем все виджеты
            $allWidgets = $widgetManager->getAllWidgets();
            $hiddenWidgets = [];

            foreach ($allWidgets as $widgetId => $widgetData) {
                // Проверяем, видим ли виджет
                if (method_exists($widgetManager, 'isWidgetVisible')) {
                    if (!$widgetManager->isWidgetVisible($widgetId)) {
                        $hiddenWidgets[] = [
                            'id' => $widgetId,
                            'title' => $widgetData['title'] ?? 'Без названия',
                            'icon' => $widgetData['icon'] ?? 'bi-question-circle',
                            'description' => $widgetData['description'] ?? 'Описание отсутствует',
                            'source' => $widgetData['source'] ?? 'system',
                            'plugin_name' => $widgetData['plugin_name'] ?? null
                        ];
                    }
                } else {
                    // Простая проверка через сессию
                    if (!isset($_SESSION['user_widgets'][$widgetId]) || !$_SESSION['user_widgets'][$widgetId]) {
                        $hiddenWidgets[] = [
                            'id' => $widgetId,
                            'title' => $widgetData['title'] ?? 'Без названия',
                            'icon' => $widgetData['icon'] ?? 'bi-question-circle',
                            'description' => $widgetData['description'] ?? 'Описание отсутствует',
                            'source' => $widgetData['source'] ?? 'system',
                            'plugin_name' => $widgetData['plugin_name'] ?? null
                        ];
                    }
                }
            }

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
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $widgetManager = \Core\Widgets\WidgetManager::getInstance();
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
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        $widgetManager = \Core\Widgets\WidgetManager::getInstance();
        $widget = $widgetManager->getWidget($widgetId);

        if (!$widget) {
            return $this->json(['error' => 'Widget not found'], 404);
        }

        // Рендерим HTML виджета
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