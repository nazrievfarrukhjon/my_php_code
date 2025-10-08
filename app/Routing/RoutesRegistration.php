<?php

namespace App\Routing;

use App\Cache\FileCache;
use App\Container\Container;
use App\Routing\Routes\AuthRoute;
use App\Routing\Routes\BillingRoute;
use App\Routing\Routes\BlacklistRoute;
use App\Routing\Routes\DriverRoute;
use App\Routing\Routes\ElasticsearchRoute;
use App\Routing\Routes\GeneralElasticsearchRoute;
use App\Routing\Routes\RideRoute;
use App\Routing\Routes\WelcomeRoute;
use App\Routing\Routes\WhitelistRoute;
use App\Routing\Routes\WebRoute;

class RoutesRegistration
{
    private Container $container;

    private array $routeClasses = [
        WelcomeRoute::class,
        WebRoute::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
        AuthRoute::class,
        DriverRoute::class,
        RideRoute::class,
        BillingRoute::class,
        ElasticsearchRoute::class,
        GeneralElasticsearchRoute::class,
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getRoutes(): array
    {
        $cache = new FileCache(ROOT_DIR . '/storage/endpoints.php');

        $routes = $cache->get('routes', []);

        if (empty($routes)) {
            foreach ($this->routeClasses as $routeClass) {
                $ep = new $routeClass($routes);
                $routes = $ep->getRoutes();
            }

            $cache->set('routes', $routes);
        }

        return $routes;
    }

}
