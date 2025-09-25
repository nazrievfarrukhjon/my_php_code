<?php

namespace App\Routing;


use App\Controllers\WelcomeController;

class WelcomeRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('GET', '/',WelcomeController::class, 'index', [], []);

        $this->add('GET', '/favicon.ico', WelcomeController::class, 'favicon', [], []);

        return $this->routesContainer;
    }

}