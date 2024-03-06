<?php

namespace App\Integration\Incoming\Http\Routes;

use Exception;

class Router
{
    static array $routes = [];
    private array $routeElements;
    private string $prefix = '/';

    public static function new(): Router
    {
        return new self();
    }

    protected function setPrefix(string $pref): void
    {
        $this->prefix = '/' . $pref;
    }

    public function post($uri, $controller, string $method): static
    {
        $this->setControllerAndMethod($controller, $method);

        static::$routes['POST'][$this->prefix][$uri] = $this->routeElements;

        $this->routeElements = [];

        return $this;
    }

    protected function get(string $uri, string $controller, string $method): static
    {
        $this->setControllerAndMethod($controller, $method);

        static::$routes['GET'][$this->prefix][$uri] = $this->routeElements;
        $this->routeElements = [];

        return $this;
    }

    protected function put(string $uri, string $controller, string $method): static
    {
        $this->setControllerAndMethod($controller, $method);

        static::$routes['PUT'][$this->prefix][$uri] = $this->routeElements;
        $this->routeElements = [];

        return $this;
    }

    protected function delete(string $uri, string $controller, string $method): static
    {
        $this->setControllerAndMethod($controller, $method);

        static::$routes['DELETE'][$this->prefix][$uri] = $this->routeElements;
        $this->routeElements = [];

        return $this;
    }

    protected function patch(string $url, string $controller, string $method): static
    {
        $this->setControllerAndMethod($controller, $method);

        static::$routes['PATCH'][$this->prefix][$url] = $this->routeElements;
        $this->routeElements = [];

        return $this;
    }

    public function setControllerAndMethod(string $controller, string $method): void
    {
        $this->routeElements['controller'] = $controller;
        $this->routeElements['method'] = $method;
    }

    function getRoutes(): array
    {
        $routesCacheFile =  __DIR__ . '/routes_cache.php';

        if (!file_exists($routesCacheFile) || filesize($routesCacheFile) < 2) {
            $this->populateRoutes($routesCacheFile);
        }
        return require $routesCacheFile;
    }

    private function populateRoutes($routesCacheFile): void
    {
        $routesCacheFile = $this->routeCacheFile($routesCacheFile);

        if (empty(Router::$routes)) {
            $this->populate();
        }
        $routes = Router::$routes;

        $routeContent = "<?php \n return " . var_export($routes, true) . ";";
        file_put_contents($routesCacheFile, $routeContent);
        echo("routes cached successfully.\n");
    }

    private function routeCacheFile($routesCache): string
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
}
