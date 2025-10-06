<?php

namespace App\Search;

use App\Log\LoggerInterface;
use Exception;
use Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    private $client;

    public function __construct(
        private string $host,
        private int $port,
        private string $index,
        private LoggerInterface $logger
    ) {
        $this->client = ClientBuilder::create()
            ->setHosts(["{$this->host}:{$this->port}"])
            ->build();
    }

    public function indexFraudDocument(array $data): bool
    {
        try {
            $params = [
                'index' => $this->index,
                'id' => $data['id'] ?? uniqid(),
                'body' => [
                    'first_name' => $data['first_name'] ?? '',
                    'second_name' => $data['second_name'] ?? '',
                    'third_name' => $data['third_name'] ?? '',
                    'fourth_name' => $data['fourth_name'] ?? '',
                    'birth_date' => $data['birth_date'] ?? '',
                    'type' => $data['type'] ?? 'unknown',
                    'source' => $data['source'] ?? 'manual',
                    'created_at' => date('Y-m-d H:i:s'),
                    'full_name' => $this->buildFullName($data)
                ]
            ];

            $response = $this->client->index($params);

            $this->logger->info('Document indexed in Elasticsearch', [
                'index' => $this->index,
                'document_id' => $response['_id']
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to index document', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    public function searchFraudMatches(array $queryData): array
    {
        try {
            $query = $this->buildSearchQuery($queryData);
            
            $params = [
                'index' => $this->index,
                'body' => [
                    'query' => $query,
                    'size' => 50,
                    'sort' => [
                        ['_score' => ['order' => 'desc']]
                    ]
                ]
            ];

            $response = $this->client->search($params);
            
            $results = [];
            foreach ($response['hits']['hits'] as $hit) {
                $results[] = [
                    'id' => $hit['_id'],
                    'score' => $hit['_score'],
                    'source' => $hit['_source']
                ];
            }

            $this->logger->info('Elasticsearch search completed', [
                'query' => $queryData,
                'total_hits' => $response['hits']['total']['value'],
                'results_count' => count($results)
            ]);

            return [
                'total' => $response['hits']['total']['value'],
                'results' => $results,
                'took' => $response['took']
            ];

        } catch (Exception $e) {
            $this->logger->error('Elasticsearch search failed', [
                'error' => $e->getMessage(),
                'query' => $queryData
            ]);
            return ['total' => 0, 'results' => [], 'took' => 0];
        }
    }

    private function buildSearchQuery(array $data): array
    {
        $shouldQueries = [];
        $mustQueries = [];

        if (!empty($data['birth_date'])) {
            $mustQueries[] = [
                'term' => [
                    'birth_date' => $data['birth_date']
                ]
            ];
        }

        $nameFields = ['first_name', 'second_name', 'third_name', 'fourth_name'];
        foreach ($nameFields as $field) {
            if (!empty($data[$field])) {
                $shouldQueries[] = [
                    'fuzzy' => [
                        $field => [
                            'value' => $data[$field],
                            'fuzziness' => 'AUTO',
                            'boost' => 2.0
                        ]
                    ]
                ];

                $shouldQueries[] = [
                    'match' => [
                        $field => [
                            'query' => $data[$field],
                            'boost' => 1.5
                        ]
                    ]
                ];
            }
        }

        if (!empty($data['first_name']) && !empty($data['second_name'])) {
            $fullName = $data['first_name'] . ' ' . $data['second_name'];
            $shouldQueries[] = [
                'match' => [
                    'full_name' => [
                        'query' => $fullName,
                        'boost' => 3.0
                    ]
                ]
            ];
        }

        $query = [];
        
        if (!empty($mustQueries)) {
            $query['bool']['must'] = $mustQueries;
        }
        
        if (!empty($shouldQueries)) {
            $query['bool']['should'] = $shouldQueries;
            $query['bool']['minimum_should_match'] = 1;
        }

        return empty($query) ? ['match_all' => new \stdClass()] : $query;
    }

    private function buildFullName(array $data): string
    {
        $parts = array_filter([
            $data['first_name'] ?? '',
            $data['second_name'] ?? '',
            $data['third_name'] ?? '',
            $data['fourth_name'] ?? ''
        ]);
        
        return implode(' ', $parts);
    }

    public function bulkIndex(array $documents): bool
    {
        try {
            $params = ['body' => []];

            foreach ($documents as $doc) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->index,
                        '_id' => $doc['id'] ?? uniqid()
                    ]
                ];
                
                $params['body'][] = [
                    'first_name' => $doc['first_name'] ?? '',
                    'second_name' => $doc['second_name'] ?? '',
                    'third_name' => $doc['third_name'] ?? '',
                    'fourth_name' => $doc['fourth_name'] ?? '',
                    'birth_date' => $doc['birth_date'] ?? '',
                    'type' => $doc['type'] ?? 'unknown',
                    'source' => $doc['source'] ?? 'bulk',
                    'created_at' => date('Y-m-d H:i:s'),
                    'full_name' => $this->buildFullName($doc)
                ];
            }

            $response = $this->client->bulk($params);

            $this->logger->info('Bulk index completed', [
                'documents_count' => count($documents),
                'errors' => $response['errors']
            ]);

            return !$response['errors'];
        } catch (Exception $e) {
            $this->logger->error('Bulk index failed', [
                'error' => $e->getMessage(),
                'documents_count' => count($documents)
            ]);
            return false;
        }
    }

    public function createIndex(): bool
    {
        try {
            $params = [
                'index' => $this->index,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                        'analysis' => [
                            'analyzer' => [
                                'name_analyzer' => [
                                    'type' => 'custom',
                                    'tokenizer' => 'standard',
                                    'filter' => ['lowercase', 'asciifolding']
                                ]
                            ]
                        ]
                    ],
                    'mappings' => [
                        'properties' => [
                            'first_name' => [
                                'type' => 'text',
                                'analyzer' => 'name_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'second_name' => [
                                'type' => 'text',
                                'analyzer' => 'name_analyzer',
                                'fields' => [
                                    'keyword' => ['type' => 'keyword']
                                ]
                            ],
                            'third_name' => [
                                'type' => 'text',
                                'analyzer' => 'name_analyzer'
                            ],
                            'fourth_name' => [
                                'type' => 'text',
                                'analyzer' => 'name_analyzer'
                            ],
                            'birth_date' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd'
                            ],
                            'type' => [
                                'type' => 'keyword'
                            ],
                            'source' => [
                                'type' => 'keyword'
                            ],
                            'full_name' => [
                                'type' => 'text',
                                'analyzer' => 'name_analyzer'
                            ],
                            'created_at' => [
                                'type' => 'date'
                            ]
                        ]
                    ]
                ]
            ];

            $this->client->indices()->create($params);

            $this->logger->info('Elasticsearch index created', [
                'index' => $this->index
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to create Elasticsearch index', [
                'error' => $e->getMessage(),
                'index' => $this->index
            ]);
            return false;
        }
    }

    public function getIndexStats(): array
    {
        try {
            $response = $this->client->indices()->stats(['index' => $this->index]);
            
            return [
                'index' => $this->index,
                'document_count' => $response['indices'][$this->index]['total']['docs']['count'],
                'size_in_bytes' => $response['indices'][$this->index]['total']['store']['size_in_bytes'],
                'status' => 'active'
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get index stats', [
                'error' => $e->getMessage(),
                'index' => $this->index
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function deleteIndex(): bool
    {
        try {
            $this->client->indices()->delete(['index' => $this->index]);
            
            $this->logger->info('Elasticsearch index deleted', [
                'index' => $this->index
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to delete Elasticsearch index', [
                'error' => $e->getMessage(),
                'index' => $this->index
            ]);
            return false;
        }
    }
}
