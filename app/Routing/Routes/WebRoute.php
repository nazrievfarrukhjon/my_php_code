<?php

namespace App\Routing\Routes;

use App\Controllers\ElasticsearchController;
use App\Routing\Contracts\ARoute;

class WebRoute extends ARoute
{
    public function getRoutes(): array
    {
        // Web routes (no /api prefix)
        $this->add('GET', '/', ElasticsearchController::class, 'getSwaggerUI', [], []);
        $this->add('GET', '/docs', ElasticsearchController::class, 'getSwaggerUI', [], []);
        $this->add('GET', '/swagger', ElasticsearchController::class, 'getSwaggerUI', [], []);
        $this->add('GET', '/api-docs', ElasticsearchController::class, 'getSwaggerUI', [], []);

        return $this->routesContainer;
    }
}
