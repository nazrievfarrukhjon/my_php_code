<?php

use App\App;
use App\Cache\FileCache;
use App\Cache\RedisCache;
use App\Console\Commands\ClearCacheCommand;
use App\Console\Commands\MigrateCommand;
use App\Console\Commands\RollbackCommand;
use App\Console\Console;
use App\Container\Container;
use App\Controllers\AuthController;
use App\Controllers\BillingController;
use App\Controllers\BlacklistController;
use App\Controllers\DriverLocationController;
use App\Controllers\RideController;
use App\Controllers\WelcomeController;
use App\Controllers\WhitelistController;
use App\DB\DBFactories\MysqlFactory;
use App\DB\DBFactories\PostgresFactory;
use App\DB\DBFactories\SqliteFactory;
use App\Env\Env;
use App\Http\HttpUri;
use App\Http\WebRequest;
use App\Log\Logger;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\LoggingMiddleware;
use App\Repositories\BillingRepository;
use App\Repositories\DriverLocationRepository;
use App\Repositories\RideRepository;
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

$dir = ROOT_DIR . '/storage';
$file = $dir . '/endpoints.php';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$container->setFactory('route_cache', function() use ($file) {
    return new FileCache($file);
});

$container->setFactory('redis_cache', function($c) use ($file) {
    $env = $c->get('env');
    return new RedisCache($env->get('REDIS_HOST'), $env->get('REDIS_PORT'));
});

$container->setFactory('primary_db', function($c) {
    $env = $c->get('env');
    $connection = $env->get('DB_CONNECTION');
    $factory = match($connection) {
        'mysql'  => new MysqlFactory($env),
        'pgsql'  => new PostgresFactory($env, 'primary'),
        'sqlite' => new SqliteFactory($env),
        default  => throw new RuntimeException("Unsupported DB_CONNECTION: $connection"),
    };
    return $factory->createConnection();
});

$container->setFactory('replica_db', function($c) {
    $env = $c->get('env');
    $connection = $env->get('DB_CONNECTION');
    $factory = match($connection) {
        'mysql'  => new MysqlFactory($env),
        'pgsql'  => new PostgresFactory($env, 'replica'),
        'sqlite' => new SqliteFactory($env),
        default  => throw new RuntimeException("Unsupported DB_CONNECTION: $connection"),
    };
    return $factory->createConnection();
});

$container->setFactory('whitelist', function($c) {
    return new WhitelistRepository($c->get('primary_db'));
});

$dir = ROOT_DIR . '/logs';
$file = $dir . '/app.log';

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
$container->setFactory(LoggingMiddleware::class, function($c) {
    return new LoggingMiddleware($c->get('logger'));
});

$container->setFactory(AuthMiddleware::class, function($c) {
    return new AuthMiddleware($c->get('primary_db'), $c->get('redis_cache'));
});

$container->setFactory('app', function($c) {
    return new App(
        $c->get('env'),
        $c->get('logger'),
        fn($uri, $method, $contentType, $bodyContent) => new WebRequest(
            new HttpUri($uri, $method),
            $method,
            $contentType,
            $bodyContent,
            $c->get('logger'),
            $c,
            $c->get('primary_db'),
        ),
        fn($argv) => new Console([
                    'migrate' => new MigrateCommand($c->get('primary_db'), 'migrate'),
                    'rollback' => new RollbackCommand($c->get('primary_db')),
                    'cache:clean' => new ClearCacheCommand($c->get('route_cache')),
                ])
    );
});


$container->setFactory(WelcomeController::class, function($c) {
    return fn() => new WelcomeController(
        $c->get('primary_db'),
    );
});

$container->setFactory(BlacklistController::class, function($c) {
    return fn() => new BlacklistController(
        $c->get('primary_db'),
        $c->get('replica_db'),
    );
});

$container->setFactory(WhitelistController::class, function($c) {
    return fn() => new WhitelistController(
        $c->get('primary_db'),
    );
});

$container->setFactory(AuthController::class, function($c) {
    return fn() => new AuthController(
        $c->get('primary_db'),
        $c->get('replica_db'),
        $c->get('redis_cache'),
    );
});

$container->setFactory(DriverLocationController::class, function($c) {
    return fn() => new DriverLocationController(
        new DriverLocationRepository(
            $c->get('primary_db'),
            $c->get('replica_db'),
        ),
    );
});

$container->setFactory(RideController::class, function($c) {
    return fn() => new RideController(
        new RideRepository(
            $c->get('primary_db'),
            $c->get('replica_db'),
        ),
    );
});

$container->setFactory(BillingController::class, function($c) {
    return fn() => new BillingController(
        new BillingRepository(
            $c->get('primary_db'),
            $c->get('replica_db'),
        ),
    );
});

return $container;