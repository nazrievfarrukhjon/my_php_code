<?php

namespace App\Routing\Routes;

use App\Controllers\ElasticsearchController;
use App\Routing\Contracts\ARoute;

class ElasticsearchRoute extends ARoute
{
    public function getRoutes(): array
    {
        // API routes (with /api prefix)
        $this->add('POST', '/api/elasticsearch/indices/rides', ElasticsearchController::class, 'createRidesIndex', [], []);
        
        // Specific routes for common operations (workaround for parameter matching)
        $this->add('DELETE', '/api/elasticsearch/indices/rides', ElasticsearchController::class, 'deleteRidesIndex', [], []);
        $this->add('GET', '/api/elasticsearch/indices/rides/stats', ElasticsearchController::class, 'getRidesIndexStats', [], []);
        
        // Generic parameter routes (may not work due to routing limitation)
        $this->add('DELETE', '/api/elasticsearch/indices/{indexName}', ElasticsearchController::class, 'deleteIndex', ['indexName'], []);
        $this->add('GET', '/api/elasticsearch/indices/{indexName}/stats', ElasticsearchController::class, 'getIndexStats', ['indexName'], []);

        // Ride document routes
        $this->add('POST', '/api/elasticsearch/rides', ElasticsearchController::class, 'indexRideDocument', [], []);
        $this->add('POST', '/api/elasticsearch/rides/bulk', ElasticsearchController::class, 'bulkIndexRides', [], []);
        $this->add('GET', '/api/elasticsearch/rides/search', ElasticsearchController::class, 'searchRides', [], []);

        // OpenAPI JSON spec (API endpoint)
        $this->add('GET', '/api/docs.json', ElasticsearchController::class, 'getOpenApiSpec', [], []);

        return $this->routesContainer;
    }
}
