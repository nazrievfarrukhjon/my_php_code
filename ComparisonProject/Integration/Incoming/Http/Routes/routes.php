<?php

use Integration\Incoming\Http\Routes\Router;
use Routes\BlacklistedRoutes;
use Routes\WhitelistedRoutes;

$routesCacheFile = __DIR__ . '/routes_cache.php';

function populateRoutes($routesCacheFile): void
{
    $routesCacheFile = routeCacheFile($routesCacheFile);

    if (empty(Router::$routes)) {
        populate();
    }
    $routes = Router::$routes;

    $routeContent = "<?php \n return " . var_export($routes, true) . ";";
    file_put_contents($routesCacheFile, $routeContent);
    echo("routes cached successfully.\n");
}

function routeCacheFile($routesCache): string
{
    if (!file_exists($routesCache)) {
        file_put_contents($routesCache, '');
    }
    return $routesCache;
}

function populate(): void
{
    $routes = [
        new BlacklistedRoutes(),
        new WhitelistedRoutes(),
        new ComparisonRoutes(),
    ];
    foreach ($routes as $route) {
        $route();
    }
}

try {
    if (!file_exists($routesCacheFile) || filesize($routesCacheFile) < 10) {
        populateRoutes($routesCacheFile);
    }
    Router::handleHttpRequest();
} catch (Exception $e) {
    $errorResponse = [
        'error' => $e
    ];
    echo json_encode($errorResponse);
}
