<?php

namespace App\Integration\Incoming\Http\Routers;

use App\Integration\Incoming\Http\Routers\BlacklistedRoutes;
use App\Integration\Incoming\Http\Routers\HomeRoutes;
use App\Integration\Incoming\Http\Routers\RouterBuilder;
use App\Integration\Incoming\Http\Routers\WhitelistedRoutes;

readonly class RoutePopulator
{

    public function __construct(private RouterBuilder $routerBuilder){}

    public static function new(): RoutePopulator
    {
        return new self(RouterBuilder::new());
    }

    function getRoutesFromCacheFile(): array
    {
        $routesCacheFile = __DIR__ . '/routes_cache.php';

        if (!file_exists($routesCacheFile) || filesize($routesCacheFile) < 10) {
            return require $this->storeRoutesToFile();
        }
        return require $routesCacheFile;
    }

    public function storeRoutesToFile(): static
    {
        $routesCacheFile = __DIR__ . '/routes_cache.php';

        $routesCacheFile = $this->createCacheFile($routesCacheFile);

        if (empty($this->routerBuilder->getRoutes())) {
            $this->populate();
        }
        $routes = $this->routerBuilder->getRoutes();

        $this->saveRoutesToFiles($routes, $routesCacheFile);
        echo("routes cached successfully.\n");

        return $this;
    }

    private function createCacheFile($routesCache): string
    {
        if (!file_exists($routesCache)) {
            file_put_contents($routesCache, '');
        }
        return $routesCache;
    }

    public function populate(): static
    {
        $routerClasses = [
            new HomeRoutes(),
            new BlacklistedRoutes(),
            new WhitelistedRoutes(),
        ];
        $subClassRoutes = [];
        foreach ($routerClasses as $routerClass) {
            $routerClass->setRoutes($subClassRoutes);
            $subClassRoutes = $routerClass->populate()->getRoutes();
        }

        $this->routerBuilder->setRoutes($subClassRoutes);

        return $this;
    }

    public function saveRoutesToFiles(array $routes, string $routesCacheFile): void
    {
        $routeContent = "<?php \n return " . var_export($routes, true) . ";";
        file_put_contents($routesCacheFile, $routeContent);
    }

    public function getBuiltRouts(): array
    {
        return $this->routerBuilder->getRoutes();
    }

}