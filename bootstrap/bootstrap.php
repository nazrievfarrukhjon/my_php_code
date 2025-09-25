<?php

use App\App;
use App\Cache\FileCache;
use App\Container\Container;
use App\Controllers\BlacklistController;
use App\Controllers\WelcomeController;
use App\Controllers\WhitelistController;
use App\DB\DBFactories\MysqlFactory;
use App\DB\DBFactories\PostgresFactory;
use App\DB\DBFactories\SqliteFactory;
use App\EntryPoints\Console\Commands\ClearCacheCommand;
use App\EntryPoints\Console\Commands\MigrateCommand;
use App\EntryPoints\Console\Commands\RollbackCommand;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\WebRequest;
use App\Env\Env;
use App\Log\Logger;
use App\Middlewares\LoggingMiddleware;
use App\Repositories\WhitelistRepository;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function dd(...$args): void
{
    foreach ($args as $index => $arg) {
        echo "[$index] \n";
        if (is_array($arg) || is_object($arg)) {
            print_r($arg);
        } else {
            var_dump($arg);
        }
        echo "\n";
    }

    die(0);
}


$container = new Container();

// Env service
$container->setFactory('env', function() {
    return new Env(ROOT_DIR . '/.env');
});

$container->setFactory('logger', function() {
    return Logger::getInstance();
});

$container->setFactory('route_cache', function() {
    $routeCacheDir = ROOT_DIR . '/storage/endpoints.php';
    if (!is_dir($routeCacheDir)) {
        mkdir($routeCacheDir, 0755, true);
    }
    return new FileCache($routeCacheDir);
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

$container->setFactory(LoggingMiddleware::class, function($c) {
    return new LoggingMiddleware($c->get('logger'));
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
            new Console([
                    'migrate' => new MigrateCommand($c->get('db'), 'migrate'),
                    'rollback' => new RollbackCommand($c->get('db')),
                    'cache:clean' => new ClearCacheCommand($c->get('route_cache')),
                ])
        ),
    );
});


$container->setFactory(WelcomeController::class, function($c) {
    return fn($uriParams, $bodyParams, $entityMethod, $uriEmbeddedParams) => new WelcomeController(
        $uriParams,
        $bodyParams,
        $entityMethod,
        $uriEmbeddedParams,
        $c->get('db'),
    );
});

$container->setFactory(BlacklistController::class, function($c) {
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