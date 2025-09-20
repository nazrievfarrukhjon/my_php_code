<?php

namespace App\Routing;


abstract class AEndpointSuperClass
{
    public function __construct(protected array $endpointsContainer)
    {
    }
    public abstract function endpoints(): array;

    /**
     * Add a route and resolve the service from the container
     */
    protected function add(
        string $httpMethod,
        string $uri,
        string $controller,
        string $method,
        array $argsRules = []
    ): void {
        $this->endpointsContainer[$httpMethod][$uri] = [$controller, $method, $argsRules];
    }
}
