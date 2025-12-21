<?php
declare(strict_types=1);

namespace App\Http\Middleware;

class VerifyCsrfToken
{
    /**
     * Исключения для проверки CSRF
     */
    protected $except = [
        '/api/*', // если будут API endpoints
    ];

    /**
     * Обработка запроса
     */
    public function handle($request, \Closure $next)
    {
        if ($this->isReading($request) ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)) {
            return $next($request);
        }

        throw new \Exception('CSRF token mismatch.');
    }

    /**
     * Проверяет, является ли запрос read-only
     */
    protected function isReading($request)
    {
        return in_array($request['method'], ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Проверяет, находится ли URI в исключениях
     */
    protected function inExceptArray($request)
    {
        $uri = $request['uri'] ?? '/';

        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (strpos($uri, $except) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверяет совпадение токенов
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($_SESSION['csrf_token'] ?? null) &&
            is_string($token) &&
            hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Получает токен из запроса
     */
    protected function getTokenFromRequest($request)
    {
        $token = $request['post']['_token'] ?? $request['headers']['X-CSRF-TOKEN'] ?? null;

        if (!$token && isset($request['headers']['X-XSRF-TOKEN'])) {
            $token = $request['headers']['X-XSRF-TOKEN'];
        }

        return $token;
    }
}