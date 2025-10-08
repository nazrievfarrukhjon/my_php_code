<?php

namespace App\Controllers;

use App\Search\ElasticsearchService;
use App\Log\Logger;
use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="Elasticsearch API",
 *     version="1.0.0",
 *     description="API for managing Elasticsearch indices and documents"
 * )
 * @OA\Server(
 *     url="http://localhost:8002",
 *     description="Development server"
 * )
 */
class ElasticsearchController implements ControllerInterface
{
    private ElasticsearchService $elasticsearchService;
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        
        // Get Elasticsearch configuration from environment
        $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
        $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
        
        $this->elasticsearchService = new ElasticsearchService(
            host: $host,
            port: $port,
            index: 'rides', // Default index for rides operations
            logger: $this->logger
        );
    }

    /**
     * @OA\Post(
     *     path="/api/elasticsearch/indices/rides",
     *     summary="Create rides index",
     *     description="Creates the rides index with proper mappings for geo-points and other fields",
     *     tags={"Elasticsearch Indices"},
     *     @OA\Response(
     *         response=200,
     *         description="Index created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rides index created successfully"),
     *             @OA\Property(property="index", type="string", example="rides")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create index"),
     *             @OA\Property(property="error", type="string", example="Connection refused")
     *         )
     *     )
     * )
     */
    public function createRidesIndex(): array
    {
        try {
            $success = $this->elasticsearchService->createRidesIndex();
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Rides index created successfully',
                    'index' => 'rides'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create rides index',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to create rides index', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to create rides index',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/elasticsearch/indices/rides",
     *     summary="Delete rides index",
     *     description="Deletes the rides Elasticsearch index",
     *     tags={"Elasticsearch Indices"},
     *     @OA\Response(
     *         response=200,
     *         description="Index deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Index deleted successfully"),
     *             @OA\Property(property="index", type="string", example="rides")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteRidesIndex($request): array
    {
        try {
            $success = $this->elasticsearchService->deleteIndex();

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Rides index deleted successfully',
                    'index' => 'rides'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete rides index',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete rides index', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to delete rides index',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/elasticsearch/indices/rides/stats",
     *     summary="Get rides index stats",
     *     description="Gets statistics for the rides index",
     *     tags={"Elasticsearch Indices"},
     *     @OA\Response(
     *         response=200,
     *         description="Index stats retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="stats", type="object", example={"doc_count": 100, "size_in_bytes": 50000})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getRidesIndexStats($request): array
    {
        try {
            $stats = $this->elasticsearchService->getIndexStats();

            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get rides index stats', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to get rides index stats',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/elasticsearch/indices/{indexName}",
     *     summary="Delete index",
     *     description="Deletes the specified Elasticsearch index",
     *     tags={"Elasticsearch Indices"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to delete",
     *         @OA\Schema(type="string", example="rides")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Index deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Index deleted successfully"),
     *             @OA\Property(property="index", type="string", example="rides")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteIndex($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            
            // Create a temporary service instance for the specific index
            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $tempService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $tempService->deleteIndex();
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Index deleted successfully',
                    'index' => $indexName
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete index',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete index', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to delete index',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Post(
     *     path="/api/elasticsearch/rides",
     *     summary="Index a ride document",
     *     description="Indexes a single ride document into the rides index",
     *     tags={"Ride Documents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ride_id", type="string", example="r1"),
     *             @OA\Property(property="driver_id", type="string", example="d1"),
     *             @OA\Property(property="passenger_id", type="string", example="p1"),
     *             @OA\Property(property="pickup_time", type="string", format="date-time", example="2025-10-08T08:00:00Z"),
     *             @OA\Property(property="dropoff_time", type="string", format="date-time", example="2025-10-08T08:20:00Z"),
     *             @OA\Property(
     *                 property="pickup_loc",
     *                 type="object",
     *                 @OA\Property(property="lat", type="number", format="float", example=40.7128),
     *                 @OA\Property(property="lon", type="number", format="float", example=-74.0060)
     *             ),
     *             @OA\Property(
     *                 property="dropoff_loc",
     *                 type="object",
     *                 @OA\Property(property="lat", type="number", format="float", example=40.7306),
     *                 @OA\Property(property="lon", type="number", format="float", example=-73.9352)
     *             ),
     *             @OA\Property(property="distance_km", type="number", format="float", example=12.5),
     *             @OA\Property(property="fare_usd", type="number", format="float", example=15.5),
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document indexed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Ride document indexed successfully"),
     *             @OA\Property(property="ride_id", type="string", example="r1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - invalid data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function indexRideDocument($request): array
    {
        try {
            $data = $request->bodyParams;
            $success = $this->elasticsearchService->indexRideDocument($data);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Ride document indexed successfully',
                    'ride_id' => $data['ride_id'] ?? 'unknown'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to index ride document',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to index ride document', ['error' => $e->getMessage(), 'data' => $data ?? []]);
            return [
                'success' => false,
                'message' => 'Failed to index ride document',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Post(
     *     path="/api/elasticsearch/rides/bulk",
     *     summary="Bulk index ride documents",
     *     description="Bulk indexes multiple ride documents into the rides index",
     *     tags={"Ride Documents"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="documents",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="ride_id", type="string", example="r1"),
     *                     @OA\Property(property="driver_id", type="string", example="d1"),
     *                     @OA\Property(property="passenger_id", type="string", example="p1"),
     *                     @OA\Property(property="pickup_time", type="string", format="date-time", example="2025-10-08T08:00:00Z"),
     *                     @OA\Property(property="dropoff_time", type="string", format="date-time", example="2025-10-08T08:20:00Z"),
     *                     @OA\Property(
     *                         property="pickup_loc",
     *                         type="object",
     *                         @OA\Property(property="lat", type="number", format="float", example=40.7128),
     *                         @OA\Property(property="lon", type="number", format="float", example=-74.0060)
     *                     ),
     *                     @OA\Property(
     *                         property="dropoff_loc",
     *                         type="object",
     *                         @OA\Property(property="lat", type="number", format="float", example=40.7306),
     *                         @OA\Property(property="lon", type="number", format="float", example=-73.9352)
     *                     ),
     *                     @OA\Property(property="distance_km", type="number", format="float", example=12.5),
     *                     @OA\Property(property="fare_usd", type="number", format="float", example=15.5),
     *                     @OA\Property(property="status", type="string", example="completed")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Documents indexed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bulk index completed successfully"),
     *             @OA\Property(property="documents_count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function bulkIndexRides($request): array
    {
        try {
            $data = $request->bodyParams;
            $documents = $data['documents'] ?? [];
            
            if (empty($documents)) {
                return [
                    'success' => false,
                    'message' => 'No documents provided',
                    'error' => 'documents array is required'
                ];
            }
            
            $success = $this->elasticsearchService->bulkIndexRides($documents);
            
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Bulk index completed successfully',
                    'documents_count' => count($documents)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to bulk index documents',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to bulk index rides', ['error' => $e->getMessage(), 'data' => $data ?? []]);
            return [
                'success' => false,
                'message' => 'Failed to bulk index documents',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/elasticsearch/rides/search",
     *     summary="Search rides",
     *     description="Search for rides with various filters including geo-distance search",
     *     tags={"Ride Documents"},
     *     @OA\Parameter(
     *         name="driver_id",
     *         in="query",
     *         required=false,
     *         description="Filter by driver ID",
     *         @OA\Schema(type="string", example="d1")
     *     ),
     *     @OA\Parameter(
     *         name="passenger_id",
     *         in="query",
     *         required=false,
     *         description="Filter by passenger ID",
     *         @OA\Schema(type="string", example="p1")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by ride status",
     *         @OA\Schema(type="string", example="completed")
     *     ),
     *     @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         required=false,
     *         description="Filter by pickup date from (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2025-10-01")
     *     ),
     *     @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=false,
     *         description="Filter by pickup date to (YYYY-MM-DD)",
     *         @OA\Schema(type="string", example="2025-10-31")
     *     ),
     *     @OA\Parameter(
     *         name="min_fare",
     *         in="query",
     *         required=false,
     *         description="Minimum fare filter",
     *         @OA\Schema(type="number", format="float", example=10.0)
     *     ),
     *     @OA\Parameter(
     *         name="max_fare",
     *         in="query",
     *         required=false,
     *         description="Maximum fare filter",
     *         @OA\Schema(type="number", format="float", example=50.0)
     *     ),
     *     @OA\Parameter(
     *         name="pickup_lat",
     *         in="query",
     *         required=false,
     *         description="Pickup location latitude for geo-distance search",
     *         @OA\Schema(type="number", format="float", example=40.7306)
     *     ),
     *     @OA\Parameter(
     *         name="pickup_lon",
     *         in="query",
     *         required=false,
     *         description="Pickup location longitude for geo-distance search",
     *         @OA\Schema(type="number", format="float", example=-73.9352)
     *     ),
     *     @OA\Parameter(
     *         name="distance_km",
     *         in="query",
     *         required=false,
     *         description="Distance in kilometers for geo-distance search",
     *         @OA\Schema(type="number", format="float", example=5.0)
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="Number of results to return",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="total", type="integer", example=3),
     *             @OA\Property(property="took", type="integer", example=15),
     *             @OA\Property(
     *                 property="results",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="r1"),
     *                     @OA\Property(property="score", type="number", format="float", example=1.0),
     *                     @OA\Property(
     *                         property="source",
     *                         type="object",
     *                         @OA\Property(property="ride_id", type="string", example="r1"),
     *                         @OA\Property(property="driver_id", type="string", example="d1"),
     *                         @OA\Property(property="passenger_id", type="string", example="p1"),
     *                         @OA\Property(property="status", type="string", example="completed"),
     *                         @OA\Property(property="fare_usd", type="number", format="float", example=15.5)
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function searchRides($request): array
    {
        try {
            $queryParams = $request->uriParams;
            
            // Build search parameters from query string
            $searchParams = [];
            
            if (!empty($queryParams['driver_id'])) {
                $searchParams['driver_id'] = $queryParams['driver_id'];
            }
            if (!empty($queryParams['passenger_id'])) {
                $searchParams['passenger_id'] = $queryParams['passenger_id'];
            }
            if (!empty($queryParams['status'])) {
                $searchParams['status'] = $queryParams['status'];
            }
            if (!empty($queryParams['date_from'])) {
                $searchParams['date_from'] = $queryParams['date_from'];
            }
            if (!empty($queryParams['date_to'])) {
                $searchParams['date_to'] = $queryParams['date_to'];
            }
            if (!empty($queryParams['min_fare'])) {
                $searchParams['min_fare'] = (float) $queryParams['min_fare'];
            }
            if (!empty($queryParams['max_fare'])) {
                $searchParams['max_fare'] = (float) $queryParams['max_fare'];
            }
            if (!empty($queryParams['pickup_lat']) && !empty($queryParams['pickup_lon'])) {
                $searchParams['pickup_location'] = [
                    'lat' => (float) $queryParams['pickup_lat'],
                    'lon' => (float) $queryParams['pickup_lon']
                ];
            }
            if (!empty($queryParams['distance_km'])) {
                $searchParams['distance_km'] = (float) $queryParams['distance_km'];
            }
            if (!empty($queryParams['size'])) {
                $searchParams['size'] = (int) $queryParams['size'];
            }
            
            $results = $this->elasticsearchService->searchRides($searchParams);
            
            return [
                'success' => true,
                'total' => $results['total'],
                'took' => $results['took'],
                'results' => $results['results']
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to search rides', ['error' => $e->getMessage(), 'params' => $queryParams ?? []]);
            return [
                'success' => false,
                'message' => 'Failed to search rides',
                'error' => $e->getMessage(),
                'total' => 0,
                'results' => []
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/elasticsearch/indices/{indexName}/stats",
     *     summary="Get index statistics",
     *     description="Get statistics for the specified Elasticsearch index",
     *     tags={"Elasticsearch Indices"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index",
     *         @OA\Schema(type="string", example="rides")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Index statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="index", type="string", example="rides"),
     *             @OA\Property(property="document_count", type="integer", example=150),
     *             @OA\Property(property="size_in_bytes", type="integer", example=1024000),
     *             @OA\Property(property="status", type="string", example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getIndexStats($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            
            // Create a temporary service instance for the specific index
            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $tempService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $stats = $tempService->getIndexStats();
            
            if (isset($stats['error'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to get index stats',
                    'error' => $stats['error']
                ];
            }
            
            return [
                'success' => true,
                'index' => $stats['index'],
                'document_count' => $stats['document_count'],
                'size_in_bytes' => $stats['size_in_bytes'],
                'status' => $stats['status']
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to get index stats', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to get index stats',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/docs",
     *     summary="Swagger UI",
     *     description="Interactive API documentation interface",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="Swagger UI HTML page",
     *         @OA\MediaType(mediaType="text/html")
     *     )
     * )
     */
    public function getSwaggerUI($request): array
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elasticsearch API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/api/docs.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true,
                requestInterceptor: (req) => {
                    return req;
                }
            });
        };
    </script>
</body>
</html>';
        
        // Set the content type header
        header('Content-Type: text/html');
        
        return [
            'content_type' => 'text/html',
            'body' => $html
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/docs.json",
     *     summary="OpenAPI Specification",
     *     description="OpenAPI 3.0 specification in JSON format",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="OpenAPI specification",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getOpenApiSpec($request): array
    {
        $openapi = \OpenApi\Generator::scan([__DIR__ . '/ElasticsearchController.php']);
        
        return [
            'content_type' => 'application/json',
            'body' => $openapi->toJson()
        ];
    }

    private function setupSwaggerUI(): void
    {
        $swaggerUIPath = __DIR__ . '/../../public/swagger-ui/';
        
        // Create directory if it doesn't exist
        if (!is_dir($swaggerUIPath)) {
            mkdir($swaggerUIPath, 0755, true);
        }

        // Create a simple Swagger UI HTML file
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elasticsearch API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/api/docs.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
</body>
</html>';

        file_put_contents($swaggerUIPath . 'index.html', $html);
    }
}
