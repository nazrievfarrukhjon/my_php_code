<?php

namespace App\Routing;

readonly class Router
{
    public function __construct(private array $endpoints)
    {
    }

    /**
     * @throws \Exception
     */
    public function match(string $uri, string $method): array
    {
        $urlAssociatedToController = new UrlAssociatedToController(
            $uri,
            $method,
            $this->endpoints
        );

        return [
            'controller' => $urlAssociatedToController->getController(),
            'method' => $urlAssociatedToController->getMethod(),
        ];
    }
}
