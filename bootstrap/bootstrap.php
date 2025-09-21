<?php

use App\App;
use App\Container\Container;
use App\Controllers\BlacklistController;
use App\Controllers\WelcomeController;
use App\Controllers\WhitelistController;
use App\DB\DBFactories\MysqlFactory;
use App\DB\DBFactories\PostgresFactory;
use App\DB\DBFactories\SqliteFactory;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\WebRequest;
use App\Env\Env;
use App\Log\Logger;
use App\Repositories\WhitelistRepository;
use App\Routing\BlacklistRoute;
use App\Routing\WelcomeRoutes;
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

$container->setFactory('logger', function() {
    return Logger::getInstance();
});


$container->setFactory('db', function($c) {
    $env = $c->get('env');
    $connection = $env->get('DB_CONNECTION');
    $factory = match($connection) {
        'mysql'  => new MysqlFactory($env),
        'pgsql'  => new PostgresFactory($env),
        'sqlite' => new SqliteFactory($env),
        default  => throw new RuntimeException("Unsupported DB_CONNECTION: $connection"),
    };
    return $factory->createConnection();
});



$container->setFactory('whitelist', function($c) {
    return new WhitelistRepository($c->get('db'));
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
            $c->get('logger'),
            $c,
        ),
        fn($argv) => new ConsoleWithResponse(
            new Console(
                $argv[1] ?? '',
                    $argv[2] ?? 'absent',
                    $argv[3] ?? 'absent',
                $c->get('db'),
            )
        ),
    );
});


$container->setFactory(WelcomeRoutes::class, function($c) {
    return fn($uriParams, $bodyParams, $entityMethod, $uriEmbeddedParams) => new WelcomeController(
        $uriParams,
        $bodyParams,
        $entityMethod,
        $uriEmbeddedParams,
        $c->get('db'),
    );
});

$container->setFactory(BlacklistRoute::class, function($c) {
    return fn($uriParams, $bodyParams, $entityMethod, $uriEmbeddedParams) => new BlacklistController(
        $uriParams,
        $bodyParams,
        $entityMethod,
        $uriEmbeddedParams,
        $c->get('db'),
    );
});

$container->setFactory(WhitelistController::class, function($c) {
    return fn($uriParams, $bodyParams, $entityMethod, $uriEmbeddedParams) => new WhitelistController(
        $uriParams,
        $bodyParams,
        $entityMethod,
        $uriEmbeddedParams,
        $c->get('db'),
    );
});

return $container;