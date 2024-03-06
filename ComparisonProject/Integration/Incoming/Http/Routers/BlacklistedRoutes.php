<?php

namespace App\Integration\Incoming\Http\Routers;

use App\Integration\Incoming\Http\Controllers\BlacklistedController;

class BlacklistedRoutes extends RouterBuilder
{
    public function populate(): static
    {
        $this->setPrefix('blacklisted');
        $this->post('/', BlacklistedController::class, 'save');
        $this->get('/', BlacklistedController::class, 'getAll');

        return $this;
    }

}
