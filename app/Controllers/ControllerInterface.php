<?php

namespace App\Controllers;

use App\DB\Postgres;

interface ControllerInterface
{
    public function __construct(
        array $uriParams,
        array $bodyParams,
        string $entityMethod,
        int $uriEmbeddedParam,
        Postgres $db
    );

    public function __invoke();

}