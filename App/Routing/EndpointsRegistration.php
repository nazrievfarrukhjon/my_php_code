<?php

namespace App\Routing;

use App\Routing\Endpoints\EndpointA;
use App\Routing\Endpoints\EndpointB;
use App\Routing\Endpoints\EndpointC;

class EndpointsRegistration
{
    private array $endpoints = [
        EndpointA::class,
        EndpointB::class,
        EndpointC::class,
    ];

    public function endpoints(): array
    {
        $routes = [];
        foreach ($this->endpoints as $endpoint) {
            $ep = new $endpoint($routes);
            $routes = $ep->endpoints();
        }

        return $routes;
    }

}