<?php

namespace App\Integration\Incoming\Http\Routers;

class Router
{
    public function __construct()
    {
    }

    public static function new(): Router
    {
        return new self();
    }

    public function get(string $requestedHttpMethod, string $uris)
    {
        //
        $explodedUris = explode('/', $uris);

        //params as uri /2/2/3
        $uriStr = '/';
        if (!empty($explodedUris[1])) {
            $uriStr = '';
            for ($i = 1; $i < count($explodedUris); $i++) {
                $uriStr .= '/@';
            }
        }
        $emptyUri = "/";

        //try to get from cache file
        $routesCacheFile = __DIR__ . '/routes_cache.php';

        if (file_exists($routesCacheFile)) {
            $routesFromCache = require $routesCacheFile;
            if (isset($routesFromCache[$requestedHttpMethod][$emptyUri][$uriStr])) {
                return $routesFromCache[$requestedHttpMethod][$emptyUri][$uriStr];
            }
        }

        // populate route array and get route from it
        $routes = RoutePopulator::new()
            //RAM
            ->populate()
            //cache
            ->storeRoutesToFile()
            ->getBuiltRouts();


        if (isset($routes[$requestedHttpMethod][$emptyUri][$uriStr])) {
            return $routes[$requestedHttpMethod][$emptyUri][$uriStr];
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}