<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Request;
use App\Http\Response;

abstract class Middleware implements MiddlewareInterface
{
    protected array $except = [];

    protected function shouldSkip(Request $request): bool
    {
        $uri = $request->uri();

        foreach ($this->except as $pattern) {
            if ($pattern === $uri) {
                return true;
            }

            // Поддержка wildcards
            if (str_contains($pattern, '*')) {
                $pattern = str_replace('*', '.*', $pattern);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $uri)) {
                    return true;
                }
            }
        }

        return false;
    }
}