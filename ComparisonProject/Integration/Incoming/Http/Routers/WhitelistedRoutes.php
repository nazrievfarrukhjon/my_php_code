<?php

namespace App\Integration\Incoming\Http\Routers;

use App\Comparison\Integration\Incoming\Http\Controllers\WhitelistedController;
use App\Integration\Incoming\Http\Routers\RouterBuilder;

class WhitelistedRoutes extends RouterBuilder
{
    public function populate(): static
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');

        return $this;
    }

}
