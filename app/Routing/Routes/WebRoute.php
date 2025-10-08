<?php

namespace App\Routing\Routes;

use App\Controllers\SwaggerController;
use App\Controllers\WelcomeController;
use App\Routing\Contracts\ARoute;

class WebRoute extends ARoute
{
    public function getRoutes(): array
    {
        // Web routes (no /api prefix)
        $this->add('GET', '/', WelcomeController::class, 'index', [], []);
        $this->add('GET', '/docs', SwaggerController::class, 'getCompleteSwaggerUI', [], []);
        $this->add('GET', '/swagger', SwaggerController::class, 'getCompleteSwaggerUI', [], []);
        $this->add('GET', '/api-docs', SwaggerController::class, 'getCompleteSwaggerUI', [], []);

        return $this->routesContainer;
    }
}
