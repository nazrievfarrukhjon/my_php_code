<?php

namespace App\Routing\Routes;

use App\Controllers\DriverController;
use App\Routing\Contracts\ARoute;

class DriverRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('POST', '/driver/location', DriverController::class, 'storeDriverLocation', [], []);

        return $this->routesContainer;
    }
}