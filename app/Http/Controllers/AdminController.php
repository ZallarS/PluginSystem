<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Widgets\WidgetManager;
use App\Core\Session\SessionInterface;

class AdminController extends Controller
{
    use Concerns\HasSession;

    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }

    public function dashboard()
    {
        // Проверка аутентификации теперь в middleware
        $widgetManager = WidgetManager::getInstance();
        $widgetsGrid = $widgetManager->renderWidgetsGrid();

        $data = [
            'title' => 'Панель администратора',
            'message' => 'Добро пожаловать в панель управления!',
            'widgetsGrid' => $widgetsGrid
        ];

        return $this->view('admin.dashboard', $data);
    }

    public function saveWidgets()
    {
        // Проверка аутентификации и CSRF теперь в middleware
        if ($this->request->method() !== 'POST') {
            return $this->json(['error' => 'Invalid request'], 400);
        }

        $widgets = json_decode($this->request->post('widgets', '{}'), true);

        if (!is_array($widgets)) {
            return $this->json(['error' => 'Invalid widgets data'], 400);
        }

        // Используем SessionManager для сохранения виджетов
        try {
            $session = app(SessionManager::class);
            $session->set('user_widgets', $widgets);
        } catch (\Exception $e) {
            // Fallback
            $_SESSION['user_widgets'] = $widgets;
        }

        return $this->json(['success' => true, 'message' => 'Настройки виджетов сохранены']);
    }

    public function toggleWidget()
    {
        // Проверка аутентификации и CSRF теперь в middleware
        if ($this->request->method() !== 'POST') {
            return $this->json(['error' => 'Invalid request'], 400);
        }

        $widgetId = $this->request->post('widget_id', '');
        $action = $this->request->post('action', '');

        if (!$widgetId) {
            return $this->json(['error' => 'Widget ID is required'], 400);
        }

        $widgetManager = WidgetManager::getInstance();

        if ($action === 'hide_widget') {
            $widgetManager->hideWidget($widgetId);
            return $this->json(['success' => true, 'message' => 'Виджет скрыт']);
        } elseif ($action === 'show_widget') {
            $widgetManager->showWidget($widgetId);
            $html = $widgetManager->renderWidget($widgetId);

            return $this->json([
                'success' => true,
                'message' => 'Виджет восстановлен',
                'html' => $html
            ]);
        }

        return $this->json(['error' => 'Invalid action'], 400);
    }

    public function getHiddenWidgets()
    {
        try {
            $widgetManager = WidgetManager::getInstance();
            $hiddenWidgets = $widgetManager->getHiddenWidgets();

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
        $widgetManager = WidgetManager::getInstance();
        $widget = $widgetManager->getWidget($widgetId);

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
        // Проверка аутентификации теперь в middleware
        $widgetManager = WidgetManager::getInstance();
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