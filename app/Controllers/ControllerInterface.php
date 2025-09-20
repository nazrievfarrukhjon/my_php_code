<?php

namespace App\Controllers;

use App\DB\DBConnection;

interface ControllerInterface
{
    public function __construct(
        array $uriParams,
        array $bodyParams,
        string $entityMethod,
        int $uriEmbeddedParam,
        DBConnection $db
    );

    public function __invoke();

}