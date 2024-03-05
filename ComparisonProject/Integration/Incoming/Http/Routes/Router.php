<?php

namespace Integration\Incoming\Http\Routes;

use Exception;

class Router
{
    static array $routes = [];
    private array $routeElements;
    private string $prefix = '/';

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

    public static function handleHttpRequest(): void
    {
        echo 'ss';
        die();
        $requestedUri = self::getRequestedUri();
        $requestedHttpMethod = $_SERVER['REQUEST_METHOD'];
        $routes = cachedRoutes();
        $uris = explode('/', $requestedUri);
        $uriFirst = isset($uris[1]) ? '/' . $uris[1] : '/';
        $uriSecond = isset($uris[2]) ? '/' . $uris[2] : '/';
        if (isset($routes[$requestedHttpMethod][$uriFirst][$uriSecond])) {
            $route = $routes[$requestedHttpMethod][$uriFirst][$uriSecond];
            $controller = $route['controller'];
            $method = $route['method'];

            static::callControllerMethod($controller, $method);
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    static function callControllerMethod(string $controller, string $method): void
    {
        try {
            $args = json_decode(file_get_contents('php://input'), true);
            $controllerInstance = new $controller();

            header('Content-Type: application/json');
            if ($args === null) {
                echo json_encode($controllerInstance->$method());
            } elseif (count($args) > 0) {

                echo json_encode($controllerInstance->$method($args));
            }
        } catch (Exception $e) {
            $errorResponse = [
                'error' => $e->getMessage(), $e->getCode(), $e->getTrace(),
            ];
            echo json_encode($errorResponse);
        }
    }

    private static function getRequestedUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uriParts = parse_url($uri);
        return ($uriParts['path'] === '/') ? '/' : rtrim($uriParts['path'], '/');
    }

    public function setControllerAndMethod(string $controller, string $method): void
    {
        $this->routeElements['controller'] = $controller;
        $this->routeElements['method'] = $method;
    }

}
