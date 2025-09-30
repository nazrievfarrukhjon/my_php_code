<?php

namespace App\Routing\Routes;

use App\Controllers\BillingController;
use App\Routing\Contracts\ARoute;

class BillingRoute extends ARoute
{

    public function getRoutes(): array
    {
        $this->add('POST', '/billing/charge', BillingController::class, 'charge', [], []);

        $this->add('POST', '/billing/invoices', BillingController::class, 'getStatus', [], []);

        return $this->routesContainer;
    }

}