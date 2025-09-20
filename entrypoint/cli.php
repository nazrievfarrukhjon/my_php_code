<?php

use App\App;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;

require_once __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/../bootstrap/bootstrap.php';

$env = $container->get('env');
$logger = $container->get('logger');
$db = $container->get('db');

// CLI factory
$cliFactory = fn($argv) => new ConsoleWithResponse(
    new Console(
        $argv[1] ?? '',
        $argv[2] ?? 'absent',
        $argv[3] ?? 'absent',
        $db,
    )
);

$app = new App($env, $logger, cliFactory: $cliFactory, httpRequestCallback: null);
exit($app->handleCli($argv));
