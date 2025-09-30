<?php

use Grpc\Server;

$root = __DIR__ . '/../';
define('ROOT_DIR', $root);

require_once ROOT_DIR . '/vendor/autoload.php';

$container = require ROOT_DIR . '/bootstrap/bootstrap.php';