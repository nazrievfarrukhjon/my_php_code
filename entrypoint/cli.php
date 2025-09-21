<?php

use App\App;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\Commands\ClearCacheCommand;
use App\EntryPoints\Console\Commands\MigrateCommand;
use App\EntryPoints\Console\Commands\RollbackCommand;

require_once ROOT_DIR . '/vendor/autoload.php';
$container = require ROOT_DIR . '/bootstrap/bootstrap.php';

$env = $container->get('env');
$logger = $container->get('logger');
$db = $container->get('db');
$routeCache = $container->get('route_cache');

// Register commands
$commands = [
    'migrate' => new MigrateCommand($db, 'migrate'),
    'rollback' => new RollbackCommand($db),
    'cache:clean' => new ClearCacheCommand($routeCache),
];

// CLI factory
$cliFactory = fn($argv) => new Console($commands);

$app = new App($env, $logger, cliFactory: $cliFactory, httpFactory: null);

// Example: php bin/console.php migrate
$commandName = $argv[1] ?? '';
exit($app->handleCli($argv));
