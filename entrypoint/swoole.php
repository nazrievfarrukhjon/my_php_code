<?php

use App\Http\SwooleRequestHandler;
use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$root = __DIR__ . '/../';
define('ROOT_DIR', $root);

require_once ROOT_DIR . '/vendor/autoload.php';
require_once ROOT_DIR . '/app/Http/SwooleRequest.php';

$container = require ROOT_DIR . '/bootstrap/bootstrap.php';

$logger = $container->get('logger');
$env = $container->get('env');

$host = $env->get('SWOOLE_HOST', '0.0.0.0');
$port = (int) $env->get('SWOOLE_PORT', '8080');
$workerNum = (int) $env->get('SWOOLE_WORKER_NUM', swoole_cpu_num());
$maxRequest = (int) $env->get('SWOOLE_MAX_REQUEST', 1000);

$logger->info('Starting Swoole HTTP Server', [
    'host' => $host,
    'port' => $port,
    'worker_num' => $workerNum,
    'max_request' => $maxRequest
]);

$server = new Server($host, $port);

$server->set([
    'worker_num' => $workerNum,
    'max_request' => $maxRequest,
    'enable_coroutine' => true,
    'max_coroutine' => 100000,
    'open_tcp_nodelay' => true,
    'open_cpu_affinity' => true,
    'log_level' => 0,
    'log_file' => ROOT_DIR . '/logs/swoole.log',
    'pid_file' => ROOT_DIR . '/logs/swoole.pid',
    'daemonize' => false,
]);

$swooleRequestHandler = new SwooleRequestHandler($container, $logger);

$server->on('request', function (Request $request, Response $response) use ($swooleRequestHandler, $logger) {
    try {
        $swooleRequestHandler->handle($request, $response);
    } catch (Exception $e) {
        $logger->error('Unhandled exception in Swoole request', [
            'exception' => $e,
            'uri' => $request->server['request_uri'] ?? 'unknown',
            'method' => $request->getMethod() ?? 'unknown'
        ]);
        
        $response->status(500);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['error' => 'Internal Server Error']));
    }
});

$server->on('start', function (Server $server) use ($logger, $host, $port) {
    $logger->info('Swoole HTTP Server started', [
        'host' => $host,
        'port' => $port,
        'master_pid' => $server->master_pid,
        'manager_pid' => $server->manager_pid
    ]);
    echo "Swoole HTTP Server started at http://{$host}:{$port}\n";
});

$server->on('workerStart', function (Server $server, int $workerId) use ($logger) {
    $logger->info('Swoole worker started', ['worker_id' => $workerId]);
});

$server->on('workerStop', function (Server $server, int $workerId) use ($logger) {
    $logger->info('Swoole worker stopped', ['worker_id' => $workerId]);
});

$server->on('shutdown', function (Server $server) use ($logger) {
    $logger->info('Swoole HTTP Server shutdown');
});

$server->on('managerStart', function (Server $server) use ($logger) {
    $logger->info('Swoole manager process started');
});

try {
    $server->start();
} catch (Exception $e) {
    $logger->error('Failed to start Swoole server', [
        'exception' => $e,
        'host' => $host,
        'port' => $port
    ]);
    echo "Failed to start Swoole server: " . $e->getMessage() . "\n";
    exit(1);
}
