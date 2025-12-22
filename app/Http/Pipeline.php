<?php
declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\MiddlewareInterface;

class Pipeline
{
    private ?Request $request = null;
    private array $middlewares = [];

    public function send(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function through(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function then(callable $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            $this->carry(),
            $this->prepareDestination($destination)
        );

        return $pipeline($this->request ?? Request::createFromGlobals());
    }

    private function prepareDestination(callable $destination): callable
    {
        return function ($request) use ($destination) {
            return $destination($request);
        };
    }

    private function carry(): callable
    {
        return function ($stack, $middleware) {
            return function ($request) use ($stack, $middleware) {
                if ($middleware instanceof MiddlewareInterface) {
                    return $middleware->handle($request, $stack);
                }

                // Если middleware - имя класса, создаем экземпляр
                if (is_string($middleware) && class_exists($middleware)) {
                    $middlewareInstance = new $middleware();
                    return $middlewareInstance->handle($request, $stack);
                }

                // Если middleware - функция
                if (is_callable($middleware)) {
                    return $middleware($request, $stack);
                }

                // Если middleware - массив (группа)
                if (is_array($middleware)) {
                    $groupPipeline = new Pipeline();
                    return $groupPipeline
                        ->send($request)
                        ->through($middleware)
                        ->then($stack);
                }

                throw new \InvalidArgumentException('Invalid middleware');
            };
        };
    }
}