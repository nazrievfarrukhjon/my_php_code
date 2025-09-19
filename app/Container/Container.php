<?php

namespace App\Container;


use App\Exceptions\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    private array $services = [];
    private array $factories = [];

    /**
     * Register a concrete instance
     */
    public function set($id, $service): void
    {
        $this->services[$id] = $service;
    }

    /**
     * Register a factory (lazy creation)
     */
    public function setFactory(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Retrieve service
     */
    public function get($id): mixed
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->factories[$id])) {
            $this->services[$id] = $this->factories[$id]($this);
            return $this->services[$id];
        }

        throw new class("Service {$id} not found") extends \Exception implements NotFoundExceptionInterface {
        };
    }

    /**
     * Check if service exists
     */
    public function has($id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }
}
