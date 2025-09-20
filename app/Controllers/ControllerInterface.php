<?php

namespace App\Controllers;

use App\DB\Database;

interface ControllerInterface
{
    public function __construct(
        array $uriParams,
        array $bodyParams,
        string $entityMethod,
        int $uriEmbeddedParam,
        Database $db
    );

    public function __invoke();

}