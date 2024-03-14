<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Routing\EndpointsRegistration;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(1);
}

try {
    (new \App\EntryPoint\HttpRequest(
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD'],
        $_SERVER["CONTENT_TYPE"] ?? 'application/json',
        file_get_contents('php://input'),
    ))->handle();
} catch (Exception $e) {
    echo $e->getMessage();
}