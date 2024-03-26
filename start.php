<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\EntryPoint\Console\Console;
use App\EntryPoint\Console\ConsoleWithResponse;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(1);
}

try {
    // cli
    // #[ExpectedValues(['cli', 'phpdbg', 'embed', 'apache', 'apache2handler', 'cgi-fcgi', 'cli-server', 'fpm-fcgi', 'litespeed'])]
    if (php_sapi_name() === 'cli') {
        if (isset($argv)) {
            $commandName = $argv[1];
            $argOne = $argv[2] ?? 'absent';
            $argTwo = $argv[3] ?? 'absent';
            $console = new Console($commandName, $argOne, $argTwo);
            $console->handleCliCommand();

            echo (new ConsoleWithResponse($console))->response();
            die(1);
        } else {
            dd('no args passed to cli command');
        }
    }
    //http
    (new \App\EntryPoint\HttpRequest(
        $_SERVER['REQUEST_URI'],
        $_SERVER['REQUEST_METHOD'],
        $_SERVER["CONTENT_TYPE"] ?? 'application/json',
        file_get_contents('php://input'),
    ))->handle();
} catch (Exception $e) {
    echo $e->getMessage();
}