<?php

namespace App\Integration\Incoming\Http\Routes;

use App\Integration\Incoming\Http\Routes\BlacklistedRoutes;
use App\Integration\Incoming\Http\Routes\HomeRoutes;
use App\Integration\Incoming\Http\Routes\RouterBuilder;
use App\Integration\Incoming\Http\Routes\WhitelistedRoutes;

class RoutePopulater
{
    public static function new(): RoutePopulater
    {
        return new self();
    }

    function getRoutes(): array
    {
        $routesCacheFile =  __DIR__ . '/routes_cache.php';

        if (!file_exists($routesCacheFile) || filesize($routesCacheFile) < 2) {
            $this->populateRoutes($routesCacheFile);
        }
        return require $routesCacheFile;
    }

    private function populateRoutes(string $routesCacheFile): void
    {
        $routesCacheFile = $this->createCacheFile($routesCacheFile);

        if (empty(RouterBuilder::$routes)) {
            $this->populate();
        }
        $routes = RouterBuilder::$routes;

        $this->saveRoutesToFiles($routes, $routesCacheFile);
        echo("routes cached successfully.\n");
    }

    private function createCacheFile($routesCache): string
    {
        if (!file_exists($routesCache)) {
            file_put_contents($routesCache, '');
        }
        return $routesCache;
    }

    private function populate(): void
    {
        $routes = [
            new HomeRoutes(),
            new BlacklistedRoutes(),
            new WhitelistedRoutes(),
        ];
        foreach ($routes as $route) {
            $route();
        }
    }

    public function saveRoutesToFiles(array $routes, string $routesCacheFile): void
    {
        $routeContent = "<?php \n return " . var_export($routes, true) . ";";
        file_put_contents($routesCacheFile, $routeContent);
    }

}