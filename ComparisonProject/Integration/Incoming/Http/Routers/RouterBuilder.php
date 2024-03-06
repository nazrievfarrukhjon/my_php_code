<?php

namespace App\Integration\Incoming\Http\Routers;

use Exception;

class RouterBuilder
{
    private array $routes = [];

    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }
    private array $routeElements;
    private string $prefix = '/';


    public function __construct()
    {
    }

    public static function new(): RouterBuilder
    {
        return new self();
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    protected function setPrefix(string $pref): void
    {
        $this->prefix = '/' . $pref;
    }

    public function post($uri, $controller, string $method): static
    {
        $this->routes['POST'][$this->prefix][$uri] = [$controller, $method];
        return $this;
    }

    protected function get(string $uri, string $controller, string $method): static
    {
        $this->routes['GET'][$this->prefix][$uri] = [$controller, $method];
        return $this;
    }

    protected function put(string $uri, string $controller, string $method): static
    {
        $this->routes['PUT'][$this->prefix][$uri] = [$controller, $method];
        return $this;
    }

    protected function delete(string $uri, string $controller, string $method): static
    {
        $this->routes['DELETE'][$this->prefix][$uri] = [$controller, $method];
        return $this;
    }

    protected function patch(string $url, string $controller, string $method): static
    {
        $this->routes['PATCH'][$this->prefix][$url] = [$controller, $method];
        return $this;
    }

    public function setControllerAndMethod(string $controller, string $method): void
    {
        $this->routes['controller'] = $controller;
        $this->routeElements['method'] = $method;
    }

}