<?php

namespace App\Entity;

use App\Gun;

 class EntityA implements Entity
{
    public function __construct(
        private readonly array  $uriParams,
        private readonly array  $bodyParams,
        private readonly string $entityMethod,
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