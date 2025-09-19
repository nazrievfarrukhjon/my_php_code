<?php

use App\App;
use App\Container\Container;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\MyHttpRequest;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Container $container */
$container = require __DIR__ . '/../bootstrap/bootstrap.php';

$logger = $container->get('logger');
$env = $container->get('env');

// HTTP factory
$httpFactory = fn($uri, $method, $contentType, $data) => new MyHttpRequest(
    new HttpUri($uri, $method),
    $method,
    $contentType,
    $data,
    $container->get('my_db'),
    $logger
);


try {
    $app = new App($env, $logger, null, $httpFactory);
    $app->handleHttp();
} catch (Exception $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    http_response_code(500);
    echo $e->getMessage();
}
