<?php

namespace App;

use App\DB\MyDB;
use App\Log\LoggerInterface;

readonly class Dispatcher
{
    public function __construct(
        private MyDB            $db,
        private LoggerInterface $logger
    )
    {
    }

    public function dispatch(
        string $controllerClass,
        string $method,
        array  $uriParams,
        array  $bodyParams,
        string $embeddedParam
    ): mixed
    {
        $controller = new $controllerClass(
            $uriParams,
            $bodyParams,
            $method,
            $embeddedParam,
            $this->db,
            $this->logger
        );

        return $controller();
    }
}
