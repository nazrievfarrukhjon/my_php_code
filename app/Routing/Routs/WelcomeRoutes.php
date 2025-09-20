<?php

namespace App\Routing\Routs;

use App\Controllers\WelcomeController;

class WelcomeRoutes extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->container->get('logger')->info('Registering Welcome routes');
        $this->add('GET', '/', WelcomeController::class, 'index', []);

        return $this->endpointsContainer;
    }

}