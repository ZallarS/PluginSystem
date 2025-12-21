<?php
declare(strict_types=1);

namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/admin');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // ВРЕМЕННО: простая проверка (заменить на нормальную авторизацию)
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

    public function quickLogin()
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'admin';
        $_SESSION['is_admin'] = true;

        echo "Авторизован как admin! <a href='/admin'>Перейти в админку</a>";
    }
}
