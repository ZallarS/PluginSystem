<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Widgets\WidgetManager;
use App\Core\Session\SessionInterface;

class AdminController extends Controller
{
    use Concerns\HasSession;
    private WidgetManager $widgetManager;

    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?SessionInterface $session = null,
        WidgetManager $widgetManager = null
    ) {
        parent::__construct($template, $authService, $request, $session);
        $this->widgetManager = $widgetManager ?? throw new \Exception('WidgetManager должен быть внедрен через DI');
    }

    public function dashboard()
    {
        // Проверка аутентификации теперь в middleware
        $widgetsGrid = $this->widgetManager->renderWidgetsGrid();

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
            $this->session->set('user_widgets', $widgets);
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

        $widgetManager = $this->widgetManager;

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
            $widgetManager = $this->widgetManager;
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
        $widgetManager = $this->widgetManager;
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
        $widgetManager = $this->widgetManager;
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