<?php

namespace App\Routing;

use App\Cache\FileCache;
use App\Container\Container;
use App\Routing\Routes\AuthRoute;
use App\Routing\Routes\BlacklistRoute;
use App\Routing\Routes\WelcomeRoute;
use App\Routing\Routes\WhitelistRoute;

class RoutesRegistration
{
    private Container $container;

    private array $routeClasses = [
        WelcomeRoute::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
        AuthRoute::class,
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
