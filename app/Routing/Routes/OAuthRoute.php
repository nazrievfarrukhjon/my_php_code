<?php

namespace App\Routing\Routes;

use App\Controllers\AuthController;
use App\Routing\Contracts\ARoute;

class OAuthRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('GET', '/oauth/token', AuthController::class, 'index', [], []);
        $this->add('GET', '/oauth/client', AuthController::class, 'index', [], []);
        $this->add('GET', '/oauth/revoke', AuthController::class, 'index', [], []);
    }
}