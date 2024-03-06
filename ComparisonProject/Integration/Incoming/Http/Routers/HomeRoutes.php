<?php

namespace App\Integration\Incoming\Http\Routers;

use App\Integration\Incoming\Http\Controllers\HomeController;

class HomeRoutes extends RouterBuilder
{
    public function populate(): static
    {
        $this->get('/', HomeController::class, 'index');
        $this->get('/@', HomeController::class, 'one');
        $this->get('/@/@', HomeController::class, 'two');


        return $this;
    }

}
