<?php
declare(strict_types=1);

namespace App\Http\Controllers;

/**
 * HomeController class
 *
 * Handles the home page of the application.
 * Displays the main landing page with system features.
 *
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{
    /**
     * Create a new home controller instance.
     *
     * @param \App\Core\View\TemplateEngine $template The template engine
     * @param \App\Services\AuthService|null $authService The authentication service
     * @param \App\Http\Request $request The request object
     * @param \App\Core\Session\SessionInterface|null $session The session interface (optional)
     */
    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?\App\Core\Session\SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }

    /**
     * Handle the home page request.
     *
     * Displays the main landing page with information
     * about the system and its features.
     *
     * @return \App\Http\Response The response with the rendered view
     */
    public function index()
    {
        $data = [
            'title' => 'Главная страница MVC системы',
            'message' => 'Добро пожаловать в нашу MVC систему с плагинами!',
            'features' => [
                'Модульная архитектура MVC' => 'bi-diagram-3',
                'Поддержка плагинов' => 'bi-plug',
                'Система виджетов' => 'bi-grid-3x3-gap',
                'Современный интерфейс' => 'bi-palette',
                'Безопасная аутентификация' => 'bi-shield-check',
                'Гибкая маршрутизация' => 'bi-signpost-split',
                'База данных' => 'bi-database',
                'RESTful API' => 'bi-code-slash'
            ]
        ];

        return $this->view('home.index', $data);
    }
}
