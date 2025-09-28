<?php

use App\App;
use App\Console\Commands\ClearCacheCommand;
use App\Console\Commands\FakeBlacklistCommand;
use App\Console\Commands\MigrateCommand;
use App\Console\Commands\RollbackCommand;
use App\Console\Console;

$root = __DIR__ . '/../';
define('ROOT_DIR', $root);

require_once ROOT_DIR . '/vendor/autoload.php';
$container = require ROOT_DIR . '/bootstrap/bootstrap.php';

$env = $container->get('env');
$logger = $container->get('logger');
$db = $container->get('primary_db');
$routeCache = $container->get('route_cache');

// Register commands
$commands = [
    'migrate' => new MigrateCommand($db, 'migrate'),
    'rollback' => new RollbackCommand($db),
    'cache:clean' => new ClearCacheCommand($routeCache),
    'fake:blacklist' => new FakeBlacklistCommand($container->get('primary_db')),
];

// CLI factory
$cliFactory = fn($argv) => new Console($commands);

$app = new App($env, $logger, cliFactory: $cliFactory, httpFactory: null);

// Example: php bin/console.php migrate
$commandName = $argv[1] ?? '';
exit($app->handleCli($argv));
