<?php

namespace App\Proxy;

 readonly class BlacklistProxy implements IProxy
{
    public function __construct(
        private array  $uriParams,
        private array  $bodyParams,
        private string $entityMethod,
    ) {}

    public function __invoke()
    {
        return call_user_func([$this, $this->entityMethod]);

    }

    public function index(): string
    {
        $this->uriParams;
        return 'index';
    }

    public function store(): string
    {
        return 'store';

    }

    public function update(): string
    {
        return 'update';

    }

    public function delete(): string
    {
        return 'delete';

    }
}