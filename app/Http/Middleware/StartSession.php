<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Core\Session\SessionInterface;

class StartSession extends Middleware
{
    protected array $except = [];

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // Логируем начало middleware
        error_log("StartSession middleware: Начало обработки запроса");
        
        /** @var SessionInterface $session */
        $session = app(SessionInterface::class);
        error_log("StartSession middleware: Получен экземпляр SessionInterface");
        
        $session->start();
        error_log("StartSession middleware: Сессия запущена");

        // Инициализируем user_widgets если не существует
        if (!$session->has('user_widgets')) {
            error_log("StartSession middleware: Инициализация user_widgets");
            $session->set('user_widgets', []);
        }

        return $next($request);
    }
}