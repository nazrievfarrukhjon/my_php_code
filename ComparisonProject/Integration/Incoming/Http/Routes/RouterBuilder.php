<?php

namespace App\Integration\Incoming\Http\Routes;

use Exception;

class RouterBuilder
{
    static array $routes = [];
    private array $routeElements;
    private string $prefix = '/';

    public static function new(): RouterBuilder
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

}