<?php

namespace App\Http;

class RequestDTO
{
    public function __construct(
        public array $uriParams = [],
        public array $bodyParams = [],
        public ?int $uriEmbeddedParam = null,
        public array $methodArgs = [],
    ) {}

    public function getMethodArgs(): array
    {
        return $this->methodArgs;
    }
}