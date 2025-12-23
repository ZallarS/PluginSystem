<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class Authenticate extends Middleware
{
    protected array $except = [
        '/login',
        '/quick-login',
        '/api/status'
    ];

    public function handle(Request $request, callable $next): Response
    {
        // Пропускаем проверку для исключенных маршрутов
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if (!$this->isAuthenticated($request)) {
            return $this->handleUnauthenticated($request);
        }

        return $next($request);
    }

    /**
     * Проверяет аутентификацию пользователя
     */
    protected function isAuthenticated(Request $request): bool
    {
        return isset($_SESSION['user_id']) &&
            isset($_SESSION['is_admin']) &&
            $_SESSION['is_admin'] === true;
    }

    /**
     * Обрабатывает неаутентифицированный запрос
     */
    protected function handleUnauthenticated(Request $request): Response
    {
        // Для AJAX/JSON запросов
        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        // Сохраняем текущий URL для редиректа после входа
        $_SESSION['redirect_url'] = $request->uri();

        // Для веб-запросов - редирект на страницу входа
        return Response::redirect('/login');
    }
}