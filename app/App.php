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
        private ?\Closure       $httpFactory = null,
    ) {}

    /**
     * @throws Exception
     */
    function handleHttp(): void
    {
        $method = strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $contentType = !empty($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'application/json';
        $allowed = ['application/json', 'multipart/form-data', 'application/x-www-form-urlencoded'];
        if (!in_array($contentType, $allowed, true)) {
            throw new Exception("Unsupported Content-Type: $contentType");
        }
        $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);

        $httpRequest = ($this->httpFactory)(
            $uri,
            $method,
            $contentType,
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
        $response = $cliHandler->handle($argv[1]??'');

        echo "$response\n";
        return 0;
    }
}
