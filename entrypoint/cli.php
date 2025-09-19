<?php

use App\App;

require_once __DIR__ . '/../vendor/autoload.php';
$boot = require __DIR__ . '/../bootstrap/bootstrap.php';

$app = new App($boot['env'], $boot['logger']);
exit($app->handleCli($argv));
