<?php

namespace App\Routing;

use App\Routing\Routs\BlacklistRoute;
use App\Routing\Routs\WelcomeRoutes;
use App\Routing\Routs\WhitelistRoute;

class RoutesRegistration
{
    private array $endpoints = [
        WelcomeRoutes::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
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