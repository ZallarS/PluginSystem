<?php

namespace App\Controllers;

class AuthController extends BaseController
{
    public function login()
    {
        // Если уже авторизован - редирект в админку
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/admin');
        }

        // Если форма отправлена
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // ПРОСТАЯ ПРОВЕРКА (для теста)
            if ($username === 'admin' && $password === 'admin') {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = 'admin';
                $_SESSION['is_admin'] = true;

                $this->redirect('/admin');
                return;
            } else {
                $error = 'Неверные учетные данные';
            }
        }

        echo $this->view('auth.login', [
            'title' => 'Вход в панель администратора',
            'error' => $error ?? null
        ]);
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('/');
    }

    // Быстрый вход по ссылке (для теста)
    public function quickLogin()
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['is_admin'] = true;

        echo "Авторизован как admin! <a href='/admin'>Перейти в админку</a>";
    }
}