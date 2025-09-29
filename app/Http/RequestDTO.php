<?php

namespace App\Http;

class RequestDTO
{
    public function __construct(
        public array $uriParams = [],
        public array $bodyParams = [],
        public ?int $uriEmbeddedParam = null,
    ) {}

}