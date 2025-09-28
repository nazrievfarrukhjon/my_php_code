<?php

namespace App\Routing\Routes;

use App\Controllers\BlacklistController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\LoggingMiddleware;
use App\Routing\Contracts\ARoute;

class BlacklistRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('GET', '/blacklist', BlacklistController::class, 'index', [], [
            LoggingMiddleware::class,
            AuthMiddleware::class,
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

        $this->add('POST', '/blacklist/search', BlacklistController::class, 'searchByName', [], [
            LoggingMiddleware::class,
            AuthMiddleware::class,
        ]);

        return $this->routesContainer;
    }


}