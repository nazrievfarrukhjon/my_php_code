<?php

namespace App\Integration\Incoming\Http\Routes;

use App\Integration\Incoming\Http\Controllers\HomeController;

class HomeRoutes extends Router
{
    public function __invoke(): void
    {
        $this->setPrefix('');
        $this->get('/', HomeController::class, 'index');
    }

}
