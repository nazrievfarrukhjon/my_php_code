<?php

namespace App\Routing\Contracts;


abstract class ARoute
{
    public function __construct(protected array $routesContainer)
    {
    }
    public abstract function getRoutes(): array;

    protected function add(
        string $httpMethod,
        string $uri,
        string $controller,
        string $method,
        array  $args = [],
        array  $middlewares = []
    ): void {
        $this->routesContainer[$httpMethod][$uri] = [
            'controller' => $controller,
            'method' => $method,
            'args' => $args,
            'middlewares' => $middlewares,
        ];
    }

}
