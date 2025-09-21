<?php

namespace App\Routing;

use App\Cache\FileCache;
use App\Container\Container;

class RoutesRegistration
{
    private Container $container;

    private array $endpoints = [
        WelcomeRoutes::class,
        BlacklistRoute::class,
        WhitelistRoute::class,
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function endpoints(): array
    {
        $cache = new FileCache(ROOT_DIR . '/storage/endpoints.php');

        $endpoints = $cache->get('routes', []);

        if (empty($endpoints)) {
            $routes = [];
            foreach ($this->endpoints as $endpoint) {
                $ep = new $endpoint($routes);
                $routes = $ep->endpoints();
            }

            $cache->set('routes', $routes);
        }


        return $cache->get('routes', []);
    }
}
