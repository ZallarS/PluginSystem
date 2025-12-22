<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

class HandleJsonRequests extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Если это JSON запрос, парсим тело запроса
        if ($request->isJson() && $request->method() !== 'GET') {
            $content = file_get_contents('php://input');
            $data = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // Добавляем данные из JSON в POST
                foreach ($data as $key => $value) {
                    $_POST[$key] = $value;
                }
            }
        }

        return $next($request);
    }
}