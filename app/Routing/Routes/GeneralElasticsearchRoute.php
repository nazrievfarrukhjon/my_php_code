<?php

namespace App\Routing\Routes;

use App\Controllers\GeneralElasticsearchController;
use App\Controllers\SwaggerController;
use App\Routing\Contracts\ARoute;

class GeneralElasticsearchRoute extends ARoute
{
    public function getRoutes(): array
    {
        // General Elasticsearch API routes (with /api prefix)
        
        // Index management routes
        $this->add('GET', '/api/elasticsearch/indices', GeneralElasticsearchController::class, 'listIndices', [], []);
        $this->add('POST', '/api/elasticsearch/indices', GeneralElasticsearchController::class, 'createGeneralIndex', [], []);
        $this->add('DELETE', '/api/elasticsearch/indices/{indexName}', GeneralElasticsearchController::class, 'deleteGeneralIndex', ['indexName'], []);
        $this->add('GET', '/api/elasticsearch/indices/{indexName}/stats', GeneralElasticsearchController::class, 'getGeneralIndexStats', ['indexName'], []);
        $this->add('PUT', '/api/elasticsearch/indices/{indexName}/mapping', GeneralElasticsearchController::class, 'updateGeneralMapping', ['indexName'], []);

        // Document management routes
        $this->add('POST', '/api/elasticsearch/{indexName}/documents', GeneralElasticsearchController::class, 'indexGeneralDocument', ['indexName'], []);
        $this->add('GET', '/api/elasticsearch/{indexName}/search', GeneralElasticsearchController::class, 'searchGeneralDocuments', ['indexName'], []);
        $this->add('DELETE', '/api/elasticsearch/{indexName}/documents/{documentId}', GeneralElasticsearchController::class, 'deleteGeneralDocument', ['indexName', 'documentId'], []);

        // Swagger/API documentation routes
        $this->add('GET', '/api/docs.json', SwaggerController::class, 'getCompleteOpenApiSpec', [], []);

        return $this->routesContainer;
    }
}
