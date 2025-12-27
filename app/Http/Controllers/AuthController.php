<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Request;
use App\Http\Response;
use App\Core\Session\SessionInterface;

/**
 * AuthController class
 *
 * Handles user authentication including login, logout,
 * and quick login functionality.
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    use Concerns\HasSession;

    /**
     * Create a new authentication controller instance.
     *
     * @param \App\Core\View\TemplateEngine $template The template engine
     * @param AuthService|null $authService The authentication service
     * @param Request $request The request object
     * @param SessionInterface|null $session The session interface (optional)
     */
    public function __construct(
        \App\Core\View\TemplateEngine $template,
        ?AuthService $authService,
        Request $request,
        ?SessionInterface $session = null
    ) {
        parent::__construct($template, $authService, $request, $session);
    }

    /**
     * Handle the login request.
     *
     * Displays the login form and processes login attempts.
     * Supports both normal and fallback login forms.
     *
     * @return \App\Http\Response The response with the login form or redirect
     */
    public function login()
    {

        if ($this->authService && $this->authService->isLoggedIn()) {
            return $this->redirect('/admin');
        }

        $error = null;
        $errors = [];

        if ($this->request->method() === 'POST') {
            $username = trim($this->request->post('username', ''));
            $password = $this->request->post('password', '');

            $validationErrors = $this->validate([
                'username' => 'required',
                'password' => 'required'
            ]);

            if (empty($validationErrors) && $this->authService) {
                if ($this->authService->attemptLogin($username, $password)) {
                    $this->flashMessage('Вы успешно вошли в систему', 'success');
                    return $this->redirect('/admin');
                } else {
                    $error = 'Неверные учетные данные';
                    sleep(1);
                }
            } else {
                $errors = $validationErrors;
            }
        }

        try {
            return $this->view('auth.login', [
                'title' => 'Вход в панель администратора',
                'error' => $error,
                'errors' => $errors,
                'csrf_token' => $this->authService ? $this->authService->getCsrfToken() : ''
            ]);
        } catch (\Exception $e) {
            return $this->showSimpleLoginForm($error, $errors,
                $this->authService ? $this->authService->getCsrfToken() : '');
        }
    }

    /**
     * Show a simple login form as fallback.
     *
     * Displays a basic login form when the template engine
     * fails to render the normal login form.
     *
     * @param string|null $error The error message
     * @param array $errors The validation errors
     * @param string $csrfToken The CSRF token
     * @return Response The response with the simple login form
     */
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

    /**
     * Handle the logout request.
     *
     * Logs out the current user and redirects to the home page.
     *
     * @return Response The response with the logout confirmation
     */
    public function logout(): Response
    {
        if ($this->authService) {
            $this->authService->logout();
        } else {
            $this->session()->destroy();
        }

        $this->flashMessage('Вы успешно вышли из системы', 'success');
        return $this->redirect('/');
    }

    /**
     * Handle the quick login request.
     *
     * Allows quick login for development environments only.
     * Includes security checks to prevent unauthorized access.
     *
     * @return Response The response with the quick login result
     */
    public function quickLogin(): Response
    {
        // Быстрый вход ТОЛЬКО для локальной разработки
        $allowedEnvironments = ['local', 'development', 'testing'];
        $currentEnv = env('APP_ENV', 'production');

        // Проверяем, что мы в разрешенном окружении
        if (!in_array($currentEnv, $allowedEnvironments)) {
            // Логируем попытку несанкционированного доступа
            if (class_exists('App\Core\Logger')) {
                $logger = \App\Core\Logger::getInstance();
                $logger->warning('Quick login attempt in production', [
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
            }

            // Возвращаем 404, чтобы не раскрывать существование метода
            return new Response('Page not found', 404);
        }

        // Дополнительная проверка по IP для development окружения
        if ($currentEnv === 'development') {
            $allowedIps = env('DEVELOPMENT_IPS', '127.0.0.1');
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

            if (!in_array($clientIp, explode(',', $allowedIps))) {
                return new Response('Access denied', 403);
            }
        }

        $username = 'admin';
        $password = env('ADMIN_PASSWORD', 'admin');

        if ($this->authService && $this->authService->attemptLogin($username, $password)) {
            // Используем session flash
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $this->flashMessage('Быстрый вход выполнен', 'success');
            } catch (\Exception $e) {
                $this->flashMessage('Ошибка быстрого входа', 'error');
            }

            return $this->redirect('/admin');
        } else {
            // Используем session flash для ошибки
            try {
                $session = app(\App\Core\Session\SessionManager::class);
                $this->flashMessage('Ошибка быстрого входа', 'error');
            } catch (\Exception $e) {
                $this->flashMessage('Ошибка быстрого входа', 'error');
            }

            return $this->redirect('/');
        }
    }
}
