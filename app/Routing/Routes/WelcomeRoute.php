<?php

namespace App\Routing\Routes;


use App\Controllers\WelcomeController;
use App\Routing\Contracts\ARoute;

class WelcomeRoute extends ARoute
{

    public function getRoutes(): array
    {
        // Keep root path for web interface
        $this->add('GET', '/',WelcomeController::class, 'index', [], []);

        $this->add('GET', '/favicon.ico', WelcomeController::class, 'favicon', [], []);

        return $this->routesContainer;
    }

}