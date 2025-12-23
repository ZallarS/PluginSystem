<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Services\AuthService;

class VerifyCsrfToken extends Middleware
{
    protected array $except = [
        '/api/*',
        '/quick-login'
    ];

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if ($this->isReading($request)) {
            return $next($request);
        }

        /** @var AuthService $authService */
        $authService = app(AuthService::class);

        if (!$authService->validateCsrfToken($this->getTokenFromRequest($request))) {
            return $this->handleTokenMismatch($request);
        }

        return $next($request);
    }

    protected function isReading(Request $request): bool
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);

        return isset($_SESSION['csrf_token']) &&
            hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    protected function getTokenFromRequest(Request $request)
    {
        $token = $request->post('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $request->header('X-XSRF-TOKEN')) {
            $token = $request->header('X-XSRF-TOKEN');
        }

        return $token;
    }

    protected function handleTokenMismatch(Request $request): Response
    {
        if ($request->isJson() || $request->isAjax()) {
            return Response::json(['error' => 'Invalid CSRF token'], 403);
        }

        $_SESSION['flash_error'] = 'Недействительный CSRF токен';
        $redirectUrl = $request->server('HTTP_REFERER', '/');

        return new Response('', 302, ['Location' => $redirectUrl]);
    }
}