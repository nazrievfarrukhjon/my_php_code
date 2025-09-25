<?php

namespace App\Routing;

use App\Cache\FileCache;
use App\Container\Container;

class RoutesRegistration
{
    private Container $container;

    private array $routeClasses = [
        WelcomeRoute::class,
        BlacklistARoute::class,
        WhitelistRoute::class,
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getRoutes(): array
    {
        $cache = new FileCache(ROOT_DIR . '/storage/endpoints.php');

        $routesFromCache = $cache->get('routes', []);

        if (empty($routesFromCache)) {
            $routes = [];
            foreach ($this->routeClasses as $routeClass) {
                $ep = new $routeClass($routes);
                $routes = $ep->getRoutes();
            }

            $cache->set('routes', $routes);
        }


        return $cache->get('routes', []);
    }
}
