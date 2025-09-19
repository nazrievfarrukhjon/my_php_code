<?php

use App\App;
use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;

require_once __DIR__ . '/../vendor/autoload.php';
$boot = require __DIR__ . '/../bootstrap/bootstrap.php';

$env = $boot['env'];
$logger = $boot['logger'];

// CLI factory
$cliFactory = fn($argv) => new ConsoleWithResponse(
    new Console(
        $argv[1] ?? '',
        $argv[2] ?? 'absent',
        $argv[3] ?? 'absent'
    )
);

$app = new App($env, $logger, cliFactory: $cliFactory, httpRequestCallback: null);
exit($app->handleCli($argv));
