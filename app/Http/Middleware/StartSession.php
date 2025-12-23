<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;
use App\Core\Session\SessionManager;

class StartSession extends Middleware
{
    protected array $except = [];

    public function handle(Request $request, callable $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        /** @var SessionManager $session */
        $session = app(SessionManager::class);
        $session->start();

        // Инициализируем user_widgets если не существует
        if (!$session->has('user_widgets')) {
            $session->set('user_widgets', []);
        }

        return $next($request);
    }
}