<?php

namespace App;

use App\Env\Env;
use App\Log\LoggerInterface;
use Exception;

readonly class App
{
    public function __construct(
        private Env             $env,
        private LoggerInterface $logger,
        private ?\Closure       $cliFactory = null,
        private ?\Closure       $httpRequestCallback = null,
    ) {}

    /**
     * @throws Exception
     */
    function handleHttp(): void
    {
        $this->logger->info('qwe', ['qwe' => 'qwe']);

        $method = strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $allowed = ['application/json', 'multipart/form-data', 'application/x-www-form-urlencoded'];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? 'application/json';
        if (!in_array($contentType, $allowed, true)) {
            throw new Exception("Unsupported Content-Type: $contentType");
        }
        $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

        $httpRequest = ($this->httpRequestCallback)(
            $uri,
            $method,
            $contentType,
            [
                'file_get_contents' => file_get_contents('php://input'),
                'post' => $_POST,
                'files' => $_FILES,
            ]
        );

        $this->logger->info('qwe', ['qwe' => 'qwe']);
        $httpRequest->handle();
    }

    function handleCli(array $argv): int
    {
        $cliHandler = ($this->cliFactory)($argv);
        $response = $cliHandler->response();

        echo "$response\n";
        return 0;
    }
}
