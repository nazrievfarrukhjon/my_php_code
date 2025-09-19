<?php

namespace App\Routing;

use App\Cache\Cache;
use App\Routing\Routs\BlacklistRoute;
use App\Routing\Routs\WelcomeRoutes;
use App\Routing\Routs\WhitelistRoute;

class RoutesRegistration
{

    private Cache $cache;

    public function __construct()
    {
        $this->cache =  new Cache();
    }

    private array $endpoints = [
        WelcomeRoutes::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
    ];
    public function endpoints(): array
    {
        $endpointsFromCache = $this->cache->endpoints();
        if (!empty($endpointsFromCache)) {
            return $endpointsFromCache;
        }

        //
        $routes = [];
        foreach ($this->endpoints as $endpoint) {
            $ep = new $endpoint($routes);
            $routes = $ep->endpoints();
        }

        //
        $this->cache->storeEndpoints($routes);

        return $routes;
    }




}