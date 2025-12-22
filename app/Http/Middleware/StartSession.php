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
            // Простая инициализация сессии без сложной логики
            session_start();

            // Инициализируем CSRF токен если его нет
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            // Инициализируем user_widgets если не существует
            if (!isset($_SESSION['user_widgets'])) {
                $_SESSION['user_widgets'] = [];
            }
        }

        return $next($request);
    }
}