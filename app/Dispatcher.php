<?php

namespace App;

use App\DB\DatabaseConnectionInterface;
use App\DB\Postgres;
use App\Log\LoggerInterface;

readonly class Dispatcher
{
    public function __construct(
        private DatabaseConnectionInterface        $db,
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

        $this->logger->info("2 Dispatching to controller: $controllerClass, method: $method");

        return $controller();
    }
}
