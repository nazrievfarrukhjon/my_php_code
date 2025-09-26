<?php

namespace App\Middlewares;

readonly class MiddlewareDispatcher
{
    public function __construct(private array $middlewares = [])
    {
    }

    public function dispatch(array $request, callable $controller)
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($next, MiddlewareInterface $middleware) => fn($req) => $middleware->handle($req, $next),
            $controller
        );

        return $pipeline($request);
    }
}
