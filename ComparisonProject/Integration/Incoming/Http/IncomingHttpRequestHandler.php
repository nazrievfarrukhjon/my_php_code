<?php

namespace App\Integration\Incoming\Http;

use App\Integration\Incoming\Http\Routers\RoutePopulator;
use App\Integration\Incoming\Http\Routers\Router;
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

        $routes = Router::new()
            ->get($requestedHttpMethod, $requestedUri);

        $controller = $routes[0];
        $method = $routes[1];

        $this->callControllerMethod($controller, $method, $requestedUri);
    }

    private function getRequestedUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uriParts = parse_url($uri);
        return ($uriParts['path'] === '/') ? '/' : rtrim($uriParts['path'], '/');
    }

    private function callControllerMethod(string $controller, string $method, string $requestedUri): void
    {
        try {
            $args = json_decode(file_get_contents('php://input'), true);
            $controllerInstance = new $controller();

            header('Content-Type: application/json');
            $uri = explode('/', $requestedUri);

            if (!empty($uri[1])) {
                $uriSize = count($uri);
                if ($uriSize === 2) {
                    echo json_encode($controllerInstance->$method($uri[1]));
                } elseif ($uriSize === 3) {
                    //dd($method, $controllerInstance);
                    echo json_encode($controllerInstance->$method($uri[1], $uri[2]));
                } elseif ($uriSize === 4) {
                    echo json_encode($controllerInstance->$method($uri[1], $uri[2], $uri[3]));
                }
            } else {
                //todo
                if ($args === null) {
                    echo json_encode($controllerInstance->$method());
                } elseif (count($args) > 0) {
                    echo json_encode($controllerInstance->$method($args));
                }
            }
        } catch (Exception $e) {
            $errorResponse = [
                'error' => $e->getMessage(), $e->getCode(), $e->getTrace(),
            ];
            echo json_encode($errorResponse);
        }
    }

}