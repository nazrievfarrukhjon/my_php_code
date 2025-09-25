<?php

namespace App\Routing;

use App\Container\Container;
use App\Log\LoggerInterface;
use App\Middlewares\MiddlewareDispatcher;
use Exception;

readonly class HttpHandler
{
    public function __construct(
        private string          $httpUri,
        private string          $httpMethod,
        private array           $routes,
        private LoggerInterface $logger,
    ) {}

    /**
     * @throws Exception
     */
    private function getController(): string
    {
        $this->logger->info('Checking route existence', [
            'httpMethod' => $this->httpMethod,
            'httpUri' => $this->httpUri,
            'routesForMethod' => array_keys($this->routes[$this->httpMethod] ?? [])
        ]);

        if (isset($this->routes[$this->httpMethod][$this->httpUri])) {
            return $this->routes[$this->httpMethod][$this->httpUri]['controller'];
        }

        throw new Exception('getController: not found');
    }

    /**
     * @throws Exception
     */
    private function getMethod(): string
    {
        if (isset($this->routes[$this->httpMethod][$this->httpUri])) {
            return $this->routes[$this->httpMethod][$this->httpUri]['method'];
        }

        throw new Exception('getMethod: not found');
    }

    private function getMiddleware(): string|array
    {
        if (isset($this->routes[$this->httpMethod][$this->httpUri])) {
            return $this->routes[$this->httpMethod][$this->httpUri]['middlewares'] ?? [];
        }

        throw new Exception('getMiddleware: not found');
    }

    /**
     * @throws Exception
     */
    public function getMethodArgs(): array
    {
        if (isset($this->routes[$this->httpMethod][$this->httpUri])) {
            return $this->routes[$this->httpMethod][$this->httpUri]['args'] ?? [];
        }

        throw new Exception('getMethodArgs: not found');
    }

    /**
     * @throws Exception
     */
    public function handleRequest(array $request, Container $container)
    {
        $controllerClass = $this->getController();
        $method = $this->getMethod();
        $middlewares = $this->getMiddleware();

        $middlewareInstances = array_map(fn($mwClass) => $container->get($mwClass), $middlewares);

        $controllerCallable = function($req) use ($controllerClass, $method, $container) {

            $controllerFactory = $container->get($controllerClass);

            $controller = $controllerFactory(
                $req['uriParams'] ?? [],
                $req['bodyParams'] ?? [],
                $method,
                $req['uriEmbeddedParam'] ?? null
            );

            return $controller();
        };

        $dispatcher = new MiddlewareDispatcher($middlewareInstances);
        return $dispatcher->dispatch($request, $controllerCallable);
    }


}