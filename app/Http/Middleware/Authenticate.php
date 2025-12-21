<?php
declare(strict_types=1);

namespace App\Http\Middleware;

class Authenticate
{
    /**
     * Проверяет аутентификацию пользователя
     */
    public function handle($request, \Closure $next)
    {
        if (!$this->isAuthenticated()) {
            if ($this->isApiRequest($request)) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }

            $_SESSION['redirect_url'] = $request['uri'] ?? '/';
            header('Location: /login');
            exit;
        }

        return $next($request);
    }

    /**
     * Проверяет, аутентифицирован ли пользователь
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Проверяет, является ли запрос API запросом
     */
    protected function isApiRequest($request)
    {
        $accept = $request['headers']['Accept'] ?? '';
        return strpos($accept, 'application/json') !== false ||
            (isset($request['headers']['X-Requested-With']) &&
                $request['headers']['X-Requested-With'] === 'XMLHttpRequest');
    }
}