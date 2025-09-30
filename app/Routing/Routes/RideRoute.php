<?php

namespace App\Routing\Routes;

use App\Controllers\RideController;
use App\Routing\Contracts\ARoute;

class RideRoute extends ARoute
{
    public function getRoutes(): array
    {
        $this->add('POST', '/rides/request', RideController::class, 'requestRide', [], []);

        $this->add('POST', '/rides/status', RideController::class, 'getStatus', [], []);

        $this->add('POST', '/rides/pending', RideController::class, 'pending', [], []);

        $this->add('POST', '/rides/accept', RideController::class, 'acceptRide', [], []);

        $this->add('POST', '/rides/start', RideController::class, 'startRide', [], []);

        $this->add('POST', '/rides/complete', RideController::class, 'completeRide', [], []);

        $this->add('POST', '/rides/nearby', RideController::class, 'nearby', [], []);

        return $this->routesContainer;
    }

}