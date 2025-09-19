<?php

use App\App;

require_once __DIR__ . '/../vendor/autoload.php';
$bootstrap = require __DIR__ . '/../bootstrap/bootstrap.php';
$logger = $bootstrap['logger'];
$env = $bootstrap['env'];

try {
    $app = new App($env, $logger);
    $app->handleHttp();
} catch (Exception $e) {
    $logger?->error($e->getMessage(), ['exception' => $e]);
    echo $e->getMessage();
}