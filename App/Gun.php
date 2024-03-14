<?php

namespace App;

class Gun
{
    public function __construct(
        private readonly array  $uriParams,
        private readonly array  $bodyParams,
        private readonly string $entityMethod,
        private readonly array  $methodArguments
    )
    {
    }

    public function fire()
    {


    }
}