<?php

namespace App\Routing;


use App\Controllers\WelcomeController;

class WelcomeRoutes extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/',WelcomeController::class, 'index', []);

        return $this->endpointsContainer;
    }

}