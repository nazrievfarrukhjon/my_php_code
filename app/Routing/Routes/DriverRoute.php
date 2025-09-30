<?php

namespace App\Routing\Routes;

use App\Controllers\DriverLocationController;
use App\Routing\Contracts\ARoute;

class DriverRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('POST', '/driver/location', DriverLocationController::class, 'storeDriverLocation', [], []);

        return $this->routesContainer;
    }
}