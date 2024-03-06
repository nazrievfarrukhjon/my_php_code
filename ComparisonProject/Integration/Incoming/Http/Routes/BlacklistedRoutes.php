<?php

namespace App\Integration\Incoming\Http\Routes;

use App\Integration\Incoming\Http\Controllers\BlacklistedController;

class BlacklistedRoutes extends Router
{
    public function __invoke(): void
    {
        $this->setPrefix('blacklisted');
        $this->post('/', BlacklistedController::class, 'save');
        $this->get('/', BlacklistedController::class, 'getAll');

    }

}
