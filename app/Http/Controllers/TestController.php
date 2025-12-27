<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Session\SessionInterface;

/**
 * TestController class
 *
 * A simple controller for testing system functionality.
 * Displays system status and available actions.
 *
 * @package App\Http\Controllers
 */
class TestController extends Controller
{
    use Concerns\HasSession;

    /**
     * Create a new test controller instance.
     *
     * @param \App\Core\View\TemplateEngine $template The template engine
     * @param \App\Services\AuthService|null $authService The authentication service
     * @param \App\Http\Request $request The request object
     * @param SessionInterface|null $session The session interface (optional)
     */
    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }

    /**
     * Display the system test page.
     *
     * Shows the current system status including authentication,
     * template engine, and session status, along with navigation links.
     *
     * @return void This method outputs HTML directly
     */
    public function index()
    {
        echo "<h1>✅ System Test - ВСЕ РАБОТАЕТ!</h1>";
        echo "<p>Контроллер успешно загружен!</p>";

        echo "<h3>Состояние системы:</h3>";
        echo "<ul>";
        echo "<li>AuthService: " . ($this->authService ? "✅ Доступен" : "❌ Не доступен") . "</li>";
        echo "<li>Template Engine: " . ($this->template ? "✅ Доступен" : "❌ Не доступен") . "</li>";
        echo "<li>Вход выполнен: " . ($this->isLoggedIn() ? "✅ Да" : "❌ Нет") . "</li>";
        echo "<li>Сессия: " . session_status() . " (2 = PHP_SESSION_ACTIVE)</li>";
        echo "</ul>";

        echo "<h3>Действия:</h3>";
        echo '<p><a href="/">🏠 На главную</a></p>';
        echo '<p><a href="/admin">⚙️ В админку</a></p>';
        echo '<p><a href="/login">🔑 Войти</a></p>';
    }
}
