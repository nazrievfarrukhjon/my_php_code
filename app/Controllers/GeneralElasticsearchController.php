<?php

namespace App\Controllers;

use App\Search\ElasticsearchService;
use App\Log\Logger;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="General Elasticsearch",
 *     description="General API for managing any Elasticsearch indices and documents"
 * )
 */
class GeneralElasticsearchController implements ControllerInterface
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    /**
     * @OA\Get(
     *     path="/api/elasticsearch/indices",
     *     summary="List all indices",
     *     description="Lists all Elasticsearch indices with their basic information",
     *     tags={"General Elasticsearch"},
     *     @OA\Response(
     *         response=200,
     *         description="Indices listed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="indices", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function listIndices($request): array
    {
        try {
            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, '', $this->logger);
            $indices = $elasticsearchService->listAllIndices();

            return [
                'success' => true,
                'indices' => $indices
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to list indices', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Failed to list indices',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Post(
     *     path="/api/elasticsearch/indices",
     *     summary="Create index",
     *     description="Creates a new Elasticsearch index with optional mapping",
     *     tags={"General Elasticsearch"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="index_name", type="string", example="my_index"),
     *             @OA\Property(property="mapping", type="object"),
     *             @OA\Property(property="settings", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Index created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Index created successfully"),
     *             @OA\Property(property="index", type="string", example="my_index")
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
    public function createGeneralIndex($request): array
    {
        try {
            $data = $request->bodyParams;
            $indexName = $data['index_name'] ?? '';
            $mapping = $data['mapping'] ?? [];
            $settings = $data['settings'] ?? [];

            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $elasticsearchService->createGeneralIndex($mapping, $settings);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Index created successfully',
                    'index' => $indexName
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create index',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to create index', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to create index',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/elasticsearch/indices/{indexName}",
     *     summary="Delete index",
     *     description="Deletes the specified Elasticsearch index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to delete",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Index deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Index deleted successfully"),
     *             @OA\Property(property="index", type="string", example="my_index")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteGeneralIndex($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            
            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $elasticsearchService->deleteIndex();

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
     * @OA\Get(
     *     path="/api/elasticsearch/indices/{indexName}/stats",
     *     summary="Get index stats",
     *     description="Gets statistics for the specified index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to get stats for",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Index stats retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="stats", type="object", example={"index": "my_index", "document_count": 100, "size_in_bytes": 50000})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getGeneralIndexStats($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            
            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $stats = $elasticsearchService->getIndexStats();

            return [
                'success' => true,
                'stats' => $stats
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
     * @OA\Put(
     *     path="/api/elasticsearch/indices/{indexName}/mapping",
     *     summary="Update index mapping",
     *     description="Updates the mapping for the specified index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to update mapping for",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="mapping", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mapping updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mapping updated successfully"),
     *             @OA\Property(property="index", type="string", example="my_index")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateGeneralMapping($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            $data = $request->bodyParams;
            $mapping = $data['mapping'] ?? [];

            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            if (empty($mapping)) {
                return [
                    'success' => false,
                    'message' => 'Mapping is required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $elasticsearchService->updateMapping($mapping);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Mapping updated successfully',
                    'index' => $indexName
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update mapping',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to update mapping', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to update mapping',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Post(
     *     path="/api/elasticsearch/{indexName}/documents",
     *     summary="Index document",
     *     description="Indexes a document in the specified index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to index document in",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="document", type="object"),
     *             @OA\Property(property="id", type="string", example="doc_123", description="Optional document ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document indexed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document indexed successfully"),
     *             @OA\Property(property="document_id", type="string", example="doc_123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function indexGeneralDocument($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            $data = $request->bodyParams;
            $document = $data['document'] ?? [];
            $documentId = $data['id'] ?? null;

            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            if (empty($document)) {
                return [
                    'success' => false,
                    'message' => 'Document data is required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $elasticsearchService->indexDocument($document, $documentId);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Document indexed successfully',
                    'document_id' => $documentId ?? 'auto_generated'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to index document',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to index document', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to index document',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/elasticsearch/{indexName}/search",
     *     summary="Search documents",
     *     description="Searches for documents in the specified index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index to search in",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=false,
     *         description="Search query (JSON string)",
     *         @OA\Schema(type="string", example="match_all")
     *     ),
     *     @OA\Parameter(
     *         name="size",
     *         in="query",
     *         required=false,
     *         description="Number of results to return",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         required=false,
     *         description="Starting offset for results",
     *         @OA\Schema(type="integer", example=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="total", type="integer", example=100),
     *             @OA\Property(property="took", type="integer", example=5),
     *             @OA\Property(property="results", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function searchGeneralDocuments($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            $queryParams = $request->uriParams;
            
            if (empty($indexName)) {
                return [
                    'success' => false,
                    'message' => 'Index name is required'
                ];
            }

            $query = $queryParams['query'] ?? '{"match_all": {}}';
            $size = (int) ($queryParams['size'] ?? 10);
            $from = (int) ($queryParams['from'] ?? 0);

            // Parse JSON query
            $parsedQuery = json_decode($query, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'message' => 'Invalid JSON query format'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $results = $elasticsearchService->search($parsedQuery, $size, $from);

            return [
                'success' => true,
                'total' => $results['total'] ?? 0,
                'took' => $results['took'] ?? 0,
                'results' => $results['hits'] ?? []
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to search documents', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to search documents',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/elasticsearch/{indexName}/documents/{documentId}",
     *     summary="Delete document",
     *     description="Deletes a document from the specified index",
     *     tags={"General Elasticsearch"},
     *     @OA\Parameter(
     *         name="indexName",
     *         in="path",
     *         required=true,
     *         description="Name of the index",
     *         @OA\Schema(type="string", example="my_index")
     *     ),
     *     @OA\Parameter(
     *         name="documentId",
     *         in="path",
     *         required=true,
     *         description="ID of the document to delete",
     *         @OA\Schema(type="string", example="doc_123")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document deleted successfully"),
     *             @OA\Property(property="document_id", type="string", example="doc_123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteGeneralDocument($request): array
    {
        try {
            $indexName = $request->getMethodArgs()['indexName'] ?? '';
            $documentId = $request->getMethodArgs()['documentId'] ?? '';
            
            if (empty($indexName) || empty($documentId)) {
                return [
                    'success' => false,
                    'message' => 'Index name and document ID are required'
                ];
            }

            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearchService = new ElasticsearchService($host, $port, $indexName, $this->logger);
            $success = $elasticsearchService->deleteDocument($documentId);

            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Document deleted successfully',
                    'document_id' => $documentId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete document',
                    'error' => 'Unknown error occurred'
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete document', ['error' => $e->getMessage(), 'index' => $indexName ?? 'unknown', 'document_id' => $documentId ?? 'unknown']);
            return [
                'success' => false,
                'message' => 'Failed to delete document',
                'error' => $e->getMessage()
            ];
        }
    }
}
