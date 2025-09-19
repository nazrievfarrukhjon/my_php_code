<?php

namespace App;

use App\Env\Env;
use App\Log\LoggerInterface;

readonly class App
{
    public function __construct(
        private Env $env,
        private LoggerInterface $logger,
        private ?\Closure $cliFactory = null,
        private ?\Closure $httpRequestFactory = null,
    ) {}

    function handleHttp(): void
    {
        $httpRequest = ($this->httpRequestFactory)(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD'],
            $_SERVER['CONTENT_TYPE'] ?? 'application/json',
            [
                'file_get_contents' => file_get_contents('php://input'),
                'post' => $_POST,
                'files' => $_FILES,
            ]
        );

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
