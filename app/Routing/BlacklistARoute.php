<?php

namespace App\Routing;

use App\Controllers\BlacklistController;
use App\Middlewares\LoggingMiddleware;

class BlacklistARoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('GET', '/blacklist', BlacklistController::class, 'index', [], [
            LoggingMiddleware::class,
        ]);

        $this->add('POST', '/blacklist', BlacklistController::class, 'store', [], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class, // example
        ]);

        $this->add('PUT', '/blacklist', BlacklistController::class, 'update', ['int'], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class,
        ]);

        $this->add('DELETE', '/blacklist', BlacklistController::class, 'delete', ['int'], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class,
        ]);

        return $this->routesContainer;
    }


}