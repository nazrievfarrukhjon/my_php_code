<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\EntryPoints\Console\Console;
use App\EntryPoints\Console\ConsoleWithResponse;
use App\EntryPoints\Http\HttpUri;
use App\EntryPoints\Http\MyHttpRequest;
use JetBrains\PhpStorm\NoReturn;

#[NoReturn] function dd(...$args): void
{
    foreach ($args as $arg) {
        var_dump($arg);
    }
    die(1);
}

//todo optimize it
function env(string $envVarKey) {
    $filePath = __DIR__ . '/.env';
    $envVariables = [];

    if ($file = fopen($filePath, 'r')) {
        while (($line = fgets($file)) !== false) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);

            $value = trim($value, '"');

            $envVariables[$key] = $value;
        }

        fclose($file);
    } else {
        // Handle error if unable to open the file
        die("Unable to open $filePath for reading.");
    }

    return $envVariables[$envVarKey];
}

try {
    // cli
    // #[ExpectedValues(['cli', 'phpdbg', 'embed', 'apache', 'apache2handler', 'cgi-fcgi', 'cli-server', 'fpm-fcgi', 'litespeed'])]
    if (php_sapi_name() === 'cli') {
        if (isset($argv)) {
            $commandName = $argv[1];
            $argOne = $argv[2] ?? 'absent';
            $argTwo = $argv[3] ?? 'absent';

            $response = (new ConsoleWithResponse(
                    new Console($commandName, $argOne, $argTwo))
                )->response();

            echo "$response\n";

            die(1);
        } else {
            dd('no args passed to cli command');
        }
    }

    //http
    (new MyHttpRequest(
        new HttpUri(
            $_SERVER['REQUEST_URI'],
            $_SERVER['REQUEST_METHOD']
        ),
        $_SERVER['REQUEST_METHOD'],
        $_SERVER["CONTENT_TYPE"] ?? 'application/json',
        ['file_get_contents' => file_get_contents('php://input'), 'post' => $_POST, 'files' => $_FILES]
    ))->handle();
} catch (Exception $e) {
    echo $e->getMessage();
}