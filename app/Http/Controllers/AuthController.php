<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Request;
use App\Http\Response;

class AuthController extends Controller
{
    public function login()
    {
        // Проверяем сессию
        if (session_status() === PHP_SESSION_NONE) {
            require_once dirname(__DIR__, 3) . '/bootstrap/session.php';
        }

        // Если уже авторизован - редирект
        if ($this->authService && $this->authService->isLoggedIn()) {
            return $this->redirect('/admin');
        }

        $error = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Проверяем CSRF
            if (!$this->validateCsrfToken()) {
                return; // validateCsrfToken уже обработал ошибку
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Базовая валидация
            if (empty($username)) {
                $errors['username'] = 'Имя пользователя обязательно';
            }

            if (empty($password)) {
                $errors['password'] = 'Пароль обязателен';
            }

            if (empty($errors) && $this->authService) {
                if ($this->authService->attemptLogin($username, $password)) {
                    $_SESSION['flash_message'] = 'Вы успешно вошли в систему';
                    return $this->redirect('/admin');
                } else {
                    $error = 'Неверные учетные данные';
                    sleep(1); // Задержка при неудачной попытке
                }
            }
        }

        // Генерируем CSRF токен если его нет
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        try {
            return $this->view('auth.login', [
                'title' => 'Вход в панель администратора',
                'error' => $error,
                'errors' => $errors,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
        } catch (\Exception $e) {
            // Если ошибка в шаблоне, покажем простую форму
            return $this->showSimpleLoginForm($error, $errors, $_SESSION['csrf_token']);
        }
    }

    private function showSimpleLoginForm(?string $error, array $errors, string $csrfToken): Response
    {
        $html = '<!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Вход в систему | MVC System</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 50px; }
                .login-box { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h2 { text-align: center; color: #333; margin-bottom: 30px; }
                .form-group { margin-bottom: 20px; }
                label { display: block; margin-bottom: 5px; color: #555; }
                input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
                button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
                button:hover { background: #0056b3; }
                .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
                .alert { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
                .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h2>Вход в систему</h2>';

        if ($error) {
            $html .= '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
        }

        $html .= '<form method="POST" action="/login">
                    <input type="hidden" name="_token" value="' . htmlspecialchars($csrfToken) . '">
                    
                    <div class="form-group">
                        <label for="username">Имя пользователя</label>
                        <input type="text" id="username" name="username" value="' . htmlspecialchars($this->request->post('username', '')) . '" required>
                        ' . (isset($errors['username']) ? '<div class="error">' . htmlspecialchars($errors['username']) . '</div>' : '') . '
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль</label>
                        <input type="password" id="password" name="password" required>
                        ' . (isset($errors['password']) ? '<div class="error">' . htmlspecialchars($errors['password']) . '</div>' : '') . '
                    </div>
                    
                    <button type="submit">Войти</button>
                </form>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="/">На главную</a>
                </div>
            </div>
        </body>
        </html>';

        return new Response($html);
    }

    public function logout(): Response
    {
        if ($this->authService) {
            $this->authService->logout();
        } else {
            session_destroy();
        }

        $_SESSION['flash_message'] = 'Вы успешно вышли из системы';
        return $this->redirect('/');
    }

    public function quickLogin(): Response
    {
        // Только для development
        if (env('APP_ENV', 'production') === 'production') {
            return new Response('Quick login disabled in production', 403);
        }

        $username = 'admin';
        $password = env('ADMIN_PASSWORD', 'admin');

        if ($this->authService && $this->authService->attemptLogin($username, $password)) {
            $_SESSION['flash_message'] = 'Быстрый вход выполнен';
            return $this->redirect('/admin');
        } else {
            $_SESSION['flash_error'] = 'Ошибка быстрого входа';
            return $this->redirect('/');
        }
    }
}