<?php

namespace App\Integration\Incoming\Http;

use App\Integration\Incoming\Http\Routes\RoutePopulater;
use Exception;

class IncomingHttpRequestHandler
{

    public static function new(): IncomingHttpRequestHandler
    {
        return new self();
    }

    public function handle(): void
    {
        $requestedUri = $this->getRequestedUri();
        $requestedHttpMethod = $_SERVER['REQUEST_METHOD'];

        $routes = RoutePopulater::new()->getRoutes();

        $uris = explode('/', $requestedUri);
        $uriFirst = isset($uris[1]) ? '/' . $uris[1] : '/';
        $uriSecond = isset($uris[2]) ? '/' . $uris[2] : '/';

        if (isset($routes[$requestedHttpMethod][$uriFirst][$uriSecond])) {
            $route = $routes[$requestedHttpMethod][$uriFirst][$uriSecond];
            $controller = $route['controller'];
            $method = $route['method'];

            $this->callControllerMethod($controller, $method);
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    private function getRequestedUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uriParts = parse_url($uri);
        return ($uriParts['path'] === '/') ? '/' : rtrim($uriParts['path'], '/');
    }

    private function callControllerMethod(string $controller, string $method): void
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

}