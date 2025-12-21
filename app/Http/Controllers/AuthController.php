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

            // Базовая валидация
            $errors = [];

            if (empty($username)) {
                $errors['username'] = 'Имя пользователя обязательно';
            }

            if (empty($password)) {
                $errors['password'] = 'Пароль обязателен';
            }

            // Используем конфигурацию из config/auth.php
            $adminUsername = env('ADMIN_USERNAME', 'admin');
            $adminPassword = env('ADMIN_PASSWORD', 'admin');

            // Простая проверка (временное решение)
            if ($username === $adminUsername && $password === $adminPassword) {
                $_SESSION['user_id'] = 1;
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = true;

                // Генерируем CSRF токен
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $this->redirect('/admin');
                return;
            } else {
                $error = 'Неверные учетные данные';
            }
        }

        echo $this->view('auth.login', [
            'title' => 'Вход в панель администратора',
            'error' => $error ?? null,
            'errors' => $errors ?? []
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

        // Генерируем CSRF токен
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        echo "Авторизован как admin! <a href='/admin'>Перейти в админку</a>";
    }
}