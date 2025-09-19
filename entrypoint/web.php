<?php

use App\App;
use App\Container\Container;
use App\EntryPoints\Http\WebRequest;
use App\EntryPoints\Http\HttpUri;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var Container $container */
$container = require __DIR__ . '/../bootstrap/bootstrap.php';

$logger = $container->get('logger');
$env = $container->get('env');

// HTTP factory
$httpRequestCallback = fn($uri, $method, $contentType, $bodyReq) => new WebRequest(
    new HttpUri($uri, $method),
    $method,
    $contentType,
    $bodyReq,
    $container->get('my_db'),
    $logger
);


try {
    $app = new App($env, $logger, null, $httpRequestCallback);
    $app->handleHttp();
} catch (Exception $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    http_response_code(500);
    echo $e->getMessage();
}
