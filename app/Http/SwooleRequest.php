<?php

namespace App\Http;

use App\Container\Container;
use App\Log\LoggerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

readonly class SwooleRequestHandler
{
    public function __construct(
        private Container $container,
        private LoggerInterface $logger,
    ) {}

    public function handle(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        try {
            $method = strtoupper($swooleRequest->getMethod());
            $uri = $swooleRequest->server['request_uri'] ?? '/';
            $contentType = $swooleRequest->header['content-type'] ?? 'application/json';
            
            $this->setGlobalVariables($swooleRequest, $method, $uri, $contentType);
            
            $bodyContents = $this->parseBodyContents($swooleRequest, $contentType);
            
            $httpUri = new HttpUri($uri, $method);
            
            $webRequest = new WebRequest(
                $httpUri,
                $method,
                $contentType,
                $bodyContents,
                $this->logger,
                $this->container,
            );
            
            ob_start();
            $webRequest->handle();
            $output = ob_get_clean();
            
            $this->setResponseHeaders($swooleResponse, $output);
            
            $swooleResponse->end($output);
            
        } catch (\Exception $e) {
            $this->logger->error('Swoole request handling error: ' . $e->getMessage(), [
                'exception' => $e,
                'uri' => $uri ?? 'unknown',
                'method' => $method ?? 'unknown'
            ]);
            
            $swooleResponse->status(500);
            $swooleResponse->header('Content-Type', 'application/json');
            $swooleResponse->end(json_encode(['error' => 'Internal Server Error']));
        }
    }
    
    private function setGlobalVariables(SwooleRequest $swooleRequest, string $method, string $uri, string $contentType): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['CONTENT_TYPE'] = $contentType;
        $_SERVER['HTTP_HOST'] = $swooleRequest->header['host'] ?? 'localhost';
        $_SERVER['HTTP_USER_AGENT'] = $swooleRequest->header['user-agent'] ?? '';
        $_SERVER['HTTP_ACCEPT'] = $swooleRequest->header['accept'] ?? '*/*';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $swooleRequest->header['accept-language'] ?? '';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = $swooleRequest->header['accept-encoding'] ?? '';
        $_SERVER['HTTP_CONNECTION'] = $swooleRequest->header['connection'] ?? 'close';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '9501';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['QUERY_STRING'] = $swooleRequest->server['query_string'] ?? '';
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
        $_SERVER['SERVER_SOFTWARE'] = 'Swoole/1.0';
        
        if (!empty($swooleRequest->get)) {
            $_GET = $swooleRequest->get;
        } else {
            $_GET = [];
        }
        
        if (!empty($swooleRequest->post)) {
            $_POST = $swooleRequest->post;
        } else {
            $_POST = [];
        }
        
        if (!empty($swooleRequest->files)) {
            $_FILES = $swooleRequest->files;
        } else {
            $_FILES = [];
        }
        
        if (!empty($swooleRequest->cookie)) {
            $_COOKIE = $swooleRequest->cookie;
        } else {
            $_COOKIE = [];
        }
        
        $headers = [];
        foreach ($swooleRequest->header as $key => $value) {
            $headers['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }
        $_SERVER = array_merge($_SERVER, $headers);
    }
    
    private function parseBodyContents(SwooleRequest $swooleRequest, string $contentType): array
    {
        $bodyContents = [
            'file_get_contents' => $swooleRequest->getContent(),
            'post' => $_POST,
            'files' => $_FILES,
        ];
        
        if (str_contains($contentType, 'application/json')) {
            $jsonData = json_decode($swooleRequest->getContent(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $bodyContents['post'] = $jsonData ?? [];
                $_POST = $jsonData ?? [];
            }
        }
        
        return $bodyContents;
    }
    
    private function setResponseHeaders(SwooleResponse $swooleResponse, string $output): void
    {
        $jsonData = json_decode($output, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $swooleResponse->header('Content-Type', 'application/json');
        } else {
            if (str_starts_with(trim($output), '<')) {
                $swooleResponse->header('Content-Type', 'text/html');
            } else {
                $swooleResponse->header('Content-Type', 'text/plain');
            }
        }
        
        $swooleResponse->header('Access-Control-Allow-Origin', '*');
        $swooleResponse->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $swooleResponse->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
