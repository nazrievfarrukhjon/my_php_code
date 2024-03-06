<?php


use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(1);
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ComparisonProject/InterfaceAdapters/Databases/Psql/DB.php';
require_once __DIR__ . '/ComparisonProject/Integration/Incoming/Http/http_handler.php';