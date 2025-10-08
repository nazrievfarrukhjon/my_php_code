<?php

namespace App\Routing;

use App\Container\Container;
use App\Http\RequestDTO;
use App\Log\LoggerInterface;
use App\Middlewares\MiddlewareDispatcher;
use Exception;

class HttpHandler
{
    private ?array $matchedRoute = null;
    private array $routeParams = [];

    public function __construct(
        private string          $httpUri,
        private string          $httpMethod,
        private array           $routes,
        private LoggerInterface $logger,
    ) {}

    /**
     * Find matching route with parameter support
     */
    private function findMatchingRoute(): ?array
    {
        if ($this->matchedRoute !== null) {
            return $this->matchedRoute;
        }

        $this->logger->info('Checking route existence', [
            'httpMethod' => $this->httpMethod,
            'httpUri' => $this->httpUri,
            'routesForMethod' => array_keys($this->routes[$this->httpMethod] ?? [])
        ]);

        // First try exact match
        if (isset($this->routes[$this->httpMethod][$this->httpUri])) {
            $this->matchedRoute = $this->routes[$this->httpMethod][$this->httpUri];
            return $this->matchedRoute;
        }

        // Then try parameter matching
        if (isset($this->routes[$this->httpMethod])) {
            foreach ($this->routes[$this->httpMethod] as $routePattern => $routeConfig) {
                if ($this->matchesRoute($routePattern, $this->httpUri)) {
                    $this->matchedRoute = $routeConfig;
                    return $this->matchedRoute;
                }
            }
        }

        return null;
    }

    /**
     * Check if a route pattern matches the given URI
     */
    private function matchesRoute(string $routePattern, string $uri): bool
    {
        // Convert route pattern to regex
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $routePattern);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            // Extract parameter values
            $this->routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getController(): string
    {
        $route = $this->findMatchingRoute();
        if ($route) {
            return $route['controller'];
        }

        throw new Exception('getController: not found');
    }

    /**
     * @throws Exception
     */
    private function getMethod(): string
    {
        $route = $this->findMatchingRoute();
        if ($route) {
            return $route['method'];
        }

        throw new Exception('getMethod: not found');
    }

    private function getMiddleware(): string|array
    {
        $route = $this->findMatchingRoute();
        if ($route) {
            return $route['middlewares'] ?? [];
        }

        throw new Exception('getMiddleware: not found');
    }

    /**
     * @throws Exception
     */
    public function getMethodArgs(): array
    {
        $route = $this->findMatchingRoute();
        if ($route) {
            // Merge predefined args with extracted route parameters
            $predefinedArgs = $route['args'] ?? [];
            return array_merge($predefinedArgs, $this->routeParams);
        }

        throw new Exception('getMethodArgs: not found');
    }

    /**
     * @throws Exception
     */
    public function handleRequest(RequestDTO $request, Container $container)
    {
        $controllerClass = $this->getController();
        $method = $this->getMethod();
        $middlewares = $this->getMiddleware();
        $methodArgs = $this->getMethodArgs();

        // Update the request with method args
        $request->methodArgs = $methodArgs;

        $middlewareInstances = array_map(fn($mwClass) => $container->get($mwClass), $middlewares);

        $controllerCallable = function($request) use ($controllerClass, $method, $container) {

            $controllerFactory = $container->get($controllerClass);

            $controller = $controllerFactory();

            return $controller->$method($request);
        };

        $dispatcher = new MiddlewareDispatcher($middlewareInstances);
        return $dispatcher->dispatch($request, $controllerCallable);
    }


}