<?php

namespace App\Routing\Endpoints;

abstract class EndpointSuperClass
{
    protected array $endpointsContainer;
    public function __construct(array $endpointsContainer)
    {
        $this->endpointsContainer = $endpointsContainer;
    }

    public abstract function endpoints(): array;
}