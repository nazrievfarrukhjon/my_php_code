<?php

use App\App;
use App\Container\Container;
use App\DB\Postgres;
use App\DB\Sqlite;
use App\Entity\Whitelist;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\WebRequest;
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

$container = new Container();

// Env service
$container->setFactory('env', function() {
    return new Env(__DIR__ . '/../.env');
});

// Logger service (PSR-3)
$container->setFactory('logger', function() {
    $logger = new Logger('app');
//    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));
    return $logger;
});


$container->setFactory('db', function($c) {
    $env = $c->get('env');
    $connection = $env->get('DB_CONNECTION');

    $c->get('logger')->info("Using DB_CONNECTION: {$connection}");

    return match ($connection) {
        'pgsql' => new Postgres($env),
        'sqlite' => new Sqlite($env),
        default => throw new \RuntimeException("Unsupported DB_CONNECTION: {$connection}"),
    };
});


$container->setFactory('whitelist', function($c) {
    return new Whitelist($c->get('db'));
});

$container->setFactory('app', function($c) {
    return new App(
        $c->get('env'),
        $c->get('logger'),
        fn($uri, $method, $contentType, $data) => new WebRequest(
            new HttpUri($uri, $method),
            $method,
            $contentType,
            $data,
            $c->get('db'),
            $c->get('logger')
        ),
        fn($argv) => new ConsoleWithResponse(
            new Console($argv[1] ?? '', $argv[2] ?? 'absent', $argv[3] ?? 'absent')
        )
    );
});




return $container;