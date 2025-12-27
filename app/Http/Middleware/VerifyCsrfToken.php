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

        error_log("VerifyCsrfToken middleware: Начало проверки CSRF токена");
        
        /** @var AuthService $authService */
        $authService = app(AuthService::class);
        error_log("VerifyCsrfToken middleware: Получен экземпляр AuthService");

        if (!$authService->validateCsrfToken($this->getTokenFromRequest($request))) {
            return $this->handleTokenMismatch($request);
        }

        return $next($request);
    }

    protected function isReading(Request $request): bool
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    protected function getTokenFromRequest(Request $request)
    {
        $token = $request->post('_token') ?: $request->header('X-CSRF-TOKEN');

        if (!$token && $request->header('X-XSRF-TOKEN')) {
            $token = $request->header('X-XSRF-TOKEN');
        }
        
        error_log("VerifyCsrfToken middleware: Получен CSRF токен: " . ($token ? 'да' : 'нет'));
        
        return $token;
    }

    protected function handleTokenMismatch(Request $request): Response
    {
        $this->logCsrfTokenMismatch($request);

        if ($request->isJson() || $request->isAjax()) {
            error_log("VerifyCsrfToken middleware: JSON/AJAX запрос, возвращаем 403");
            return Response::json(['error' => 'Invalid CSRF token'], 403);
        }

        // Используем SessionInterface вместо прямого доступа
        $session = app(\App\Core\Session\SessionInterface::class);
        error_log("VerifyCsrfToken middleware: Установка flash сообщения");
        $session->flash('flash_error', 'Недействительный CSRF токен');

        $redirectUrl = $request->server('HTTP_REFERER', '/');
        error_log("VerifyCsrfToken middleware: Перенаправление на " . $redirectUrl);

        return new Response('', 302, ['Location' => $redirectUrl]);
    }
    
    private function logCsrfTokenMismatch(Request $request): void
    {
        $clientIp = $request->server('REMOTE_ADDR', 'unknown');
        $userAgent = $request->header('User-Agent', 'unknown');
        $requestMethod = $request->method();
        $requestUri = $request->uri();
        
        error_log("VerifyCsrfToken middleware: CSRF token mismatch detected");
        error_log("- IP: $clientIp");
        error_log("- User-Agent: $userAgent");
        error_log("- Method: $requestMethod");
        error_log("- URI: $requestUri");
        
        $tokenFromPost = $request->post('_token');
        $tokenFromHeader = $request->header('X-CSRF-TOKEN');
        $tokenFromXsrfHeader = $request->header('X-XSRF-TOKEN');
        
        error_log("- Token from POST: " . ($tokenFromPost ? 'provided' : 'missing'));
        error_log("- Token from X-CSRF-TOKEN header: " . ($tokenFromHeader ? 'provided' : 'missing'));
        error_log("- Token from X-XSRF-TOKEN header: " . ($tokenFromXsrfHeader ? 'provided' : 'missing'));
    }
}