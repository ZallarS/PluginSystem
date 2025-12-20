<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Главная страница MVC системы',
            'message' => 'Добро пожаловать в нашу MVC систему с плагинами!',
            'features' => [
                'Модульная архитектура MVC',
                'Поддержка плагинов',
                'Система тем оформления',
                'RESTful API',
                'Консольные команды',
                'Безопасная аутентификация',
                'Менеджер зависимостей',
                'Гибкая маршрутизация'
            ]
        ];

        echo $this->view('home.index', $data);
    }
}