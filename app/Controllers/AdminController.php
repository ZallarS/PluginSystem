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
}