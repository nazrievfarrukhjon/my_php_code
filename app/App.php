<?php

namespace App;

use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\MyHttpRequest;
use App\Env\Env;
use App\Log\LoggerInterface;
use Exception;

readonly class App
{
    public function __construct(
        private Env             $env,
        private LoggerInterface $logger
    ) {}

    /**
     * @throws Exception
     */
    function handleHttp(): void
    {
        (new MyHttpRequest(
            new HttpUri(
                $_SERVER['REQUEST_URI'],
                $_SERVER['REQUEST_METHOD']
            ),
            $_SERVER['REQUEST_METHOD'],
            $_SERVER["CONTENT_TYPE"] ?? 'application/json',
            [
                'file_get_contents' => file_get_contents('php://input'),
                'post' => $_POST,
                'files' => $_FILES,
            ]
        ))->handle();
    }

    function handleCli(array $argv): int
    {
        $commandName = $argv[1] ?? null;
        $argOne = $argv[2] ?? 'absent';
        $argTwo = $argv[3] ?? 'absent';

        if (!$commandName) {
            echo "No CLI command provided.\n";
            return 1;
        }

        $response = (new ConsoleWithResponse(
            new Console($commandName, $argOne, $argTwo)
        ))->response();

        echo "$response\n";
        return 0;
    }
}
