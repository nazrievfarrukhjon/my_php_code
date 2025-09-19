<?php

namespace App\Controllers;

use App\DB\MyDB;

interface ControllerInterface
{
    public function __construct(
        array $uriParams,
        array $bodyParams,
        string $entityMethod,
        int $uriEmbeddedParam,
        MyDB $myDb
    );

    public function __invoke();

}