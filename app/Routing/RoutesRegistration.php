<?php

namespace App\Routing;

use App\Cache\Cache;
use App\Container\Container;
use App\Routing\Routs\BlacklistRoute;
use App\Routing\Routs\WelcomeRoutes;
use App\Routing\Routs\WhitelistRoute;

class RoutesRegistration
{
    private Cache $cache;
    private Container $container;

    private array $endpoints = [
        WelcomeRoutes::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
    ];

    public function __construct(Container $container)
    {
        $this->cache = new Cache();
        $this->container = $container;
    }

    public function endpoints(): array
    {
        $endpointsFromCache = $this->cache->endpoints();
        if (!empty($endpointsFromCache)) {
            return $endpointsFromCache;
        }

        $routes = [];
        foreach ($this->endpoints as $endpoint) {
            $ep = new $endpoint($routes);
            $routes = $ep->endpoints();
        }

        $this->cache->storeEndpoints($routes);

        return $routes;
    }
}
