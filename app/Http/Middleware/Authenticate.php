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
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if (!$this->isAuthenticated($request)) {
            return $this->handleUnauthenticated($request);
        }

        return $next($request);
    }

    protected function isAuthenticated(Request $request): bool
    {
        /** @var SessionInterface $session */
        $session = app(SessionInterface::class);

        // Используем новые ключи через AuthService или напрямую
        try {
            $authService = app(\App\Services\AuthService::class);
            return $authService->isLoggedIn();
        } catch (\Exception $e) {
            // Fallback: проверяем напрямую
            return $session->has('auth.user_id') &&
                $session->has('auth.is_admin') &&
                $session->get('auth.is_admin') === true;
        }
    }

    protected function handleUnauthenticated(Request $request): Response
    {
        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['error' => 'Unauthorized'], 401);
        }

        /** @var SessionInterface $session */
        $session = app(SessionInterface::class);
        $session->set('redirect_url', $request->uri());

        return Response::redirect('/login');
    }
}