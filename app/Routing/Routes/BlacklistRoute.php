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
        $this->add('GET', '/api/blacklist', BlacklistController::class, 'index', [], [
            LoggingMiddleware::class,
            AuthMiddleware::class,
        ]);

        $this->add('POST', '/api/blacklist', BlacklistController::class, 'store', [], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class, // example
        ]);

        $this->add('PUT', '/api/blacklist', BlacklistController::class, 'update', ['int'], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class,
        ]);

        $this->add('DELETE', '/api/blacklist', BlacklistController::class, 'delete', ['int'], [
            LoggingMiddleware::class,
            //\App\Middlewares\AuthMiddleware::class,
        ]);

        $this->add('POST', '/api/blacklist/search', BlacklistController::class, 'searchByName', [], [
            LoggingMiddleware::class,
            AuthMiddleware::class,
        ]);

        return $this->routesContainer;
    }


}