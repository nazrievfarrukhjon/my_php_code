<?php

namespace App\Routing\Routes;

use App\Controllers\AuthController;
use App\Routing\Contracts\ARoute;

class AuthRoute extends ARoute
{
    public function getRoutes(): array
    {
        $this->add('POST', '/api/auth/login', AuthController::class, 'login', [], []);

        $this->add('POST', '/api/auth/register', AuthController::class, 'register', [], []);

        return $this->routesContainer;
    }

}