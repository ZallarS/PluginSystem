<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class StartSession extends Middleware
{
    protected array $except = [];

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        if (session_status() === PHP_SESSION_NONE) {
            // Безопасные настройки сессии
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? '1' : '0');
            ini_set('session.cookie_samesite', 'Lax');

            session_start();

            // Флаг, что сессия инициализирована middleware
            define('SESSION_STARTED_BY_MIDDLEWARE', true);

            // Инициализируем CSRF токен если его нет
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            // Инициализируем user_widgets если не существует
            if (!isset($_SESSION['user_widgets'])) {
                $_SESSION['user_widgets'] = [];
            }

            // Инициализируем счетчик запросов для регенерации сессии
            if (!isset($_SESSION['request_count'])) {
                $_SESSION['request_count'] = 0;
            }

            // Регенерируем ID сессии каждые 100 запросов
            $_SESSION['request_count']++;
            if ($_SESSION['request_count'] > 100) {
                session_regenerate_id(true);
                $_SESSION['request_count'] = 0;
            }
        }

        return $next($request);
    }
}