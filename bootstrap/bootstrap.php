<?php

use App\Env\Env;
use App\Log\Logger;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(0);
}

$env = new Env(__DIR__ . '/../.env');
$logger = new Logger();


return [
    'env'    => $env,
    'logger' => $logger,
];