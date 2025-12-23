<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?\App\Services\AuthService $authService,
        \App\Http\Request $request,
        ?\App\Core\Session\SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }

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