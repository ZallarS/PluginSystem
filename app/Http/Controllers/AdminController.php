<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Widgets\WidgetManager;
use App\Core\Session\SessionInterface;

/**
 * AdminController class
 *
 * Handles the admin dashboard and widget management.
 * Provides functionality for saving widget layouts
 * and managing widget visibility.
 *
 * @package App\Http\Controllers
 */
class AdminController extends Controller
{
    use Concerns\HasSession;
    /**
     * @var WidgetManager The widget manager instance
     */
    private WidgetManager $widgetManager;


    /**
     * Create a new admin controller instance.
     *
     * @param \App\Core\View\TemplateEngine $template The template engine
     * @param \App\Services\AuthService|null $authService The authentication service
     * @param \App\Http\Request $request The request object
     * @param SessionInterface|null $session The session interface (optional)
     * @param WidgetManager|null $widgetManager The widget manager (optional, will throw exception if null)
     * @throws \Exception If widget manager is not provided
     */
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

    /**
     * Display the admin dashboard.
     *
     * Shows the main admin interface with all widgets
     * and their current layout.
     *
     * @return \App\Http\Response The response with the dashboard view
     */
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

    /**
     * Display the hidden widgets page.
     *
     * @return \App\Http\Response The response with the hidden widgets view
     */
    public function getHiddenWidgetsPage()
    {
        // Проверка аутентификации в middleware

        return $this->view('admin.hidden-widgets', [
            'title' => 'Скрытые виджеты'
        ]);
    }

    /**
     * Save the widget layout.
     *
     * Saves the current widget arrangement to the user's session.
     *
     * @return \App\Http\Response The JSON response with the result
     */
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

    /**
     * Toggle a widget's visibility.
     *
     * Shows or hides a widget based on the specified action.
     *
     * @return \App\Http\Response The JSON response with the result
     */
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

    /**
     * Get the list of hidden widgets.
     *
     * Returns information about widgets that are currently
     * hidden from the dashboard.
     *
     * @return \App\Http\Response The JSON response with hidden widgets
     */
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
            return $this->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get information about a specific widget.
     *
     * Returns detailed information about the specified widget.
     *
     * @param string $widgetId The ID of the widget
     * @return \App\Http\Response The JSON response with widget information
     */
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

    /**
     * Get the HTML content of a widget.
     *
     * Renders the specified widget and returns its HTML content
     * along with basic information.
     *
     * @param string $widgetId The ID of the widget
     * @return \App\Http\Response The JSON response with widget HTML and info
     */
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
