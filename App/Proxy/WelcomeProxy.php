<?php

namespace App\Proxy;

use Exception;

readonly class WelcomeProxy implements IProxy
{
    public function __construct(
        private array  $uriParams,
        private array  $bodyParams,
        private string $entityMethod,
    )
    {
    }

    public function __invoke()
    {
        return call_user_func([$this, $this->entityMethod]);

    }

    /**
     * @throws Exception
     */
    public function index(): array
    {
        return ['this is welcome page'];
    }

}