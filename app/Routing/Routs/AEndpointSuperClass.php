<?php

namespace App\Routing\Routs;

abstract class AEndpointSuperClass
{
    protected array $endpointsContainer;
    public function __construct(array $endpointsContainer)
    {
        $this->endpointsContainer = $endpointsContainer;
    }

    public abstract function endpoints(): array;
}