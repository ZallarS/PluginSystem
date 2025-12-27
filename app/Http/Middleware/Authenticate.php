<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Core\Session\SessionInterface;

class Authenticate extends Middleware
{
    protected array $except = [
        '/login',
        '/quick-login',
        '/api/status'
    ];

    public function handle(Request $request, callable $next): Response
    {
        error_log("Authenticate middleware: Начало аутентификации для " . $request->uri());
        
        if ($this->shouldSkip($request)) {
            error_log("Authenticate middleware: Пропуск аутентификации для " . $request->uri());
            return $next($request);
        }

        if (!$this->isAuthenticated($request)) {
            error_log("Authenticate middleware: Пользователь не аутентифицирован, перенаправление на /login");
            return $this->handleUnauthenticated($request);
        }
        
        error_log("Authenticate middleware: Пользователь аутентифицирован, продолжение обработки");
        return $next($request);
    }

    protected function isAuthenticated(Request $request): bool
    {
        error_log("Authenticate middleware: Проверка аутентификации");
        
        /** @var SessionInterface $session */
        $session = app(SessionInterface::class);

        // Используем новые ключи через AuthService или напрямую
        try {
            $authService = app(\App\Services\AuthService::class);
            $isLoggedIn = $authService->isLoggedIn();
            error_log("Authenticate middleware: AuthService вернул " . ($isLoggedIn ? 'true' : 'false'));
            return $isLoggedIn;
        } catch (\Exception $e) {
            error_log("Authenticate middleware: Ошибка при проверке через AuthService: " . $e->getMessage());
            
            // Fallback: проверяем напрямую
            $hasUserId = $session->has('auth.user_id');
            $hasIsAdmin = $session->has('auth.is_admin');
            $isAdminValue = $session->get('auth.is_admin');
            
            error_log("Authenticate middleware: Fallback проверка - hasUserId: " . ($hasUserId ? 'true' : 'false') . ", hasIsAdmin: " . ($hasIsAdmin ? 'true' : 'false') . ", isAdminValue: " . ($isAdminValue ? 'true' : 'false'));
            
            return $hasUserId && $hasIsAdmin && $isAdminValue === true;
        }
    }

    protected function handleUnauthenticated(Request $request): Response
    {
        error_log("Authenticate middleware: Обработка неаутентифицированного запроса");
        
        if ($request->isJson() || $request->isAjax()) {
            error_log("Authenticate middleware: JSON/AJAX запрос, возвращаем 401");
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        /** @var SessionInterface $session */
        $session = app(SessionInterface::class);
        $redirectUrl = $request->uri();
        $session->set('redirect_url', $redirectUrl);
        error_log("Authenticate middleware: Установка redirect_url: " . $redirectUrl);

        return Response::redirect('/login');
    }
}