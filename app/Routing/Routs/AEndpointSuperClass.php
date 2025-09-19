<?php

namespace App\Routing\Routs;

use App\Container\Container;

abstract class AEndpointSuperClass
{
    protected array $endpointsContainer = [];
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public abstract function endpoints(): array;

    /**
     * Add a route and resolve the service from the container
     */
    protected function add(
        string $httpMethod,
        string $uri,
        string $serviceId,       // container service ID
        string $method,
        array $argsRules = []
    ): void {
        $instance = $this->container->get($serviceId);
        $this->endpointsContainer[$httpMethod][$uri] = [$instance, $method, $argsRules];
    }
}
