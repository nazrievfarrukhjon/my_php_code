<?php

namespace App\Routing\Routes;

use App\Controllers\RideController;
use App\Routing\Contracts\ARoute;

class RideRoute extends ARoute
{
    public function getRoutes(): array
    {
        $this->add('POST', '/api/rides/request', RideController::class, 'requestRide', [], []);

        $this->add('POST', '/api/rides/status', RideController::class, 'getStatus', [], []);

        $this->add('POST', '/api/rides/pending', RideController::class, 'pending', [], []);

        $this->add('POST', '/api/rides/accept', RideController::class, 'acceptRide', [], []);

        $this->add('POST', '/api/rides/start', RideController::class, 'startRide', [], []);

        $this->add('POST', '/api/rides/complete', RideController::class, 'completeRide', [], []);

        $this->add('POST', '/api/rides/nearby', RideController::class, 'nearby', [], []);

        return $this->routesContainer;
    }

}