<?php

namespace App\Routing;

use App\Routing\Routs\TestRoutes;
use App\Routing\Routs\BlacklistRoute;
use App\Routing\Routs\WelcomeRoutes;

class RoutesRegistration
{
    private array $endpoints = [
        WelcomeRoutes::class,
        TestRoutes::class,
        BlacklistRoute::class,
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