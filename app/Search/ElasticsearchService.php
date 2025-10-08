<?php

namespace App\Search;

use App\Log\LoggerInterface;
use Elastic\Elasticsearch\Client;
use Exception;
use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    private Client $client;

    public function __construct(
        private readonly string $host,
        private readonly int    $port,
        private readonly string $index,
        private readonly LoggerInterface $logger
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
                'document_count' => $response['indices'][$this->index]['primaries']['docs']['count'],
                'size_in_bytes' => $response['indices'][$this->index]['primaries']['store']['size_in_bytes'],
                'status' => $response['indices'][$this->index]['status']
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

    public function createRidesIndex(): bool
    {
        try {
            $params = [
                'index' => 'rides',
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'ride_id' => [
                                'type' => 'keyword'
                            ],
                            'driver_id' => [
                                'type' => 'keyword'
                            ],
                            'passenger_id' => [
                                'type' => 'keyword'
                            ],
                            'pickup_time' => [
                                'type' => 'date'
                            ],
                            'dropoff_time' => [
                                'type' => 'date'
                            ],
                            'pickup_loc' => [
                                'type' => 'geo_point'
                            ],
                            'dropoff_loc' => [
                                'type' => 'geo_point'
                            ],
                            'distance_km' => [
                                'type' => 'float'
                            ],
                            'fare_usd' => [
                                'type' => 'float'
                            ],
                            'status' => [
                                'type' => 'keyword'
                            ]
                        ]
                    ]
                ]
            ];

            $this->client->indices()->create($params);

            $this->logger->info('Rides index created successfully', [
                'index' => 'rides'
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to create rides index', [
                'error' => $e->getMessage(),
                'index' => 'rides'
            ]);
            return false;
        }
    }

    public function indexRideDocument(array $data): bool
    {
        try {
            $params = [
                'index' => 'rides',
                'id' => $data['ride_id'] ?? uniqid(),
                'body' => [
                    'ride_id' => $data['ride_id'] ?? '',
                    'driver_id' => $data['driver_id'] ?? '',
                    'passenger_id' => $data['passenger_id'] ?? '',
                    'pickup_time' => $data['pickup_time'] ?? null,
                    'dropoff_time' => $data['dropoff_time'] ?? null,
                    'pickup_loc' => $data['pickup_loc'] ?? null,
                    'dropoff_loc' => $data['dropoff_loc'] ?? null,
                    'distance_km' => $data['distance_km'] ?? 0.0,
                    'fare_usd' => $data['fare_usd'] ?? 0.0,
                    'status' => $data['status'] ?? 'pending'
                ]
            ];

            $response = $this->client->index($params);

            $this->logger->info('Ride document indexed in Elasticsearch', [
                'index' => 'rides',
                'document_id' => $response['_id']
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to index ride document', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            return false;
        }
    }

    public function searchRides(array $queryData): array
    {
        try {
            $query = $this->buildRideSearchQuery($queryData);
            
            $params = [
                'index' => 'rides',
                'body' => [
                    'query' => $query,
                    'size' => $queryData['size'] ?? 50,
                    'sort' => [
                        ['pickup_time' => ['order' => 'desc']]
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

            $this->logger->info('Rides search completed', [
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
            $this->logger->error('Rides search failed', [
                'error' => $e->getMessage(),
                'query' => $queryData
            ]);
            return ['total' => 0, 'results' => [], 'took' => 0];
        }
    }

    private function buildRideSearchQuery(array $data): array
    {
        $mustQueries = [];
        $shouldQueries = [];

        // Filter by driver_id
        if (!empty($data['driver_id'])) {
            $mustQueries[] = [
                'term' => [
                    'driver_id' => $data['driver_id']
                ]
            ];
        }

        // Filter by passenger_id
        if (!empty($data['passenger_id'])) {
            $mustQueries[] = [
                'term' => [
                    'passenger_id' => $data['passenger_id']
                ]
            ];
        }

        // Filter by status
        if (!empty($data['status'])) {
            $mustQueries[] = [
                'term' => [
                    'status' => $data['status']
                ]
            ];
        }

        // Filter by date range
        if (!empty($data['date_from']) || !empty($data['date_to'])) {
            $dateRange = [];
            if (!empty($data['date_from'])) {
                $dateRange['gte'] = $data['date_from'];
            }
            if (!empty($data['date_to'])) {
                $dateRange['lte'] = $data['date_to'];
            }
            
            $mustQueries[] = [
                'range' => [
                    'pickup_time' => $dateRange
                ]
            ];
        }

        // Filter by fare range
        if (!empty($data['min_fare']) || !empty($data['max_fare'])) {
            $fareRange = [];
            if (!empty($data['min_fare'])) {
                $fareRange['gte'] = $data['min_fare'];
            }
            if (!empty($data['max_fare'])) {
                $fareRange['lte'] = $data['max_fare'];
            }
            
            $mustQueries[] = [
                'range' => [
                    'fare_usd' => $fareRange
                ]
            ];
        }

        // Geo-distance search for pickup location
        if (!empty($data['pickup_location']) && !empty($data['distance_km'])) {
            $shouldQueries[] = [
                'geo_distance' => [
                    'distance' => $data['distance_km'] . 'km',
                    'pickup_loc' => $data['pickup_location']
                ]
            ];
        }

        // Geo-distance search for dropoff location
        if (!empty($data['dropoff_location']) && !empty($data['distance_km'])) {
            $shouldQueries[] = [
                'geo_distance' => [
                    'distance' => $data['distance_km'] . 'km',
                    'dropoff_loc' => $data['dropoff_location']
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

    public function bulkIndexRides(array $documents): bool
    {
        try {
            $params = ['body' => []];

            foreach ($documents as $doc) {
                $params['body'][] = [
                    'index' => [
                        '_index' => 'rides',
                        '_id' => $doc['ride_id'] ?? uniqid()
                    ]
                ];
                
                $params['body'][] = [
                    'ride_id' => $doc['ride_id'] ?? '',
                    'driver_id' => $doc['driver_id'] ?? '',
                    'passenger_id' => $doc['passenger_id'] ?? '',
                    'pickup_time' => $doc['pickup_time'] ?? null,
                    'dropoff_time' => $doc['dropoff_time'] ?? null,
                    'pickup_loc' => $doc['pickup_loc'] ?? null,
                    'dropoff_loc' => $doc['dropoff_loc'] ?? null,
                    'distance_km' => $doc['distance_km'] ?? 0.0,
                    'fare_usd' => $doc['fare_usd'] ?? 0.0,
                    'status' => $doc['status'] ?? 'pending'
                ];
            }

            $response = $this->client->bulk($params);

            $this->logger->info('Bulk rides index completed', [
                'documents_count' => count($documents),
                'errors' => $response['errors']
            ]);

            return !$response['errors'];
        } catch (Exception $e) {
            $this->logger->error('Bulk rides index failed', [
                'error' => $e->getMessage(),
                'documents_count' => count($documents)
            ]);
            return false;
        }
    }

    public function bulkIndexRidesFromJson(string $jsonData): bool
    {
        try {
            // Parse the JSON data
            $data = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data: ' . json_last_error_msg());
            }

            $params = ['body' => []];

            // Process each document in the bulk data
            foreach ($data as $item) {
                if (isset($item['index'])) {
                    // This is an index action
                    $params['body'][] = [
                        'index' => [
                            '_index' => 'rides',
                            '_id' => $item['index']['_id'] ?? null
                        ]
                    ];
                } elseif (isset($item['ride_id'])) {
                    // This is the document data
                    $params['body'][] = [
                        'ride_id' => $item['ride_id'] ?? '',
                        'driver_id' => $item['driver_id'] ?? '',
                        'passenger_id' => $item['passenger_id'] ?? '',
                        'pickup_time' => $item['pickup_time'] ?? null,
                        'dropoff_time' => $item['dropoff_time'] ?? null,
                        'pickup_loc' => $item['pickup_loc'] ?? null,
                        'dropoff_loc' => $item['dropoff_loc'] ?? null,
                        'distance_km' => $item['distance_km'] ?? 0.0,
                        'fare_usd' => $item['fare_usd'] ?? 0.0,
                        'status' => $item['status'] ?? 'pending'
                    ];
                }
            }

            $response = $this->client->bulk($params);

            $this->logger->info('Bulk rides index from JSON completed', [
                'documents_count' => count($data) / 2, // Each document has 2 entries (index + data)
                'errors' => $response['errors']
            ]);

            return !$response['errors'];
        } catch (Exception $e) {
            $this->logger->error('Bulk rides index from JSON failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function executeBulkRidesOperation(array $operations): bool
    {
        try {
            $params = ['body' => []];

            foreach ($operations as $operation) {
                if (isset($operation['index'])) {
                    // Index operation
                    $params['body'][] = [
                        'index' => [
                            '_index' => 'rides',
                            '_id' => $operation['index']['_id'] ?? null
                        ]
                    ];
                } elseif (isset($operation['update'])) {
                    // Update operation
                    $params['body'][] = [
                        'update' => [
                            '_index' => 'rides',
                            '_id' => $operation['update']['_id'] ?? null
                        ]
                    ];
                } elseif (isset($operation['delete'])) {
                    // Delete operation
                    $params['body'][] = [
                        'delete' => [
                            '_index' => 'rides',
                            '_id' => $operation['delete']['_id'] ?? null
                        ]
                    ];
                } elseif (isset($operation['ride_id'])) {
                    // Document data
                    $params['body'][] = [
                        'ride_id' => $operation['ride_id'] ?? '',
                        'driver_id' => $operation['driver_id'] ?? '',
                        'passenger_id' => $operation['passenger_id'] ?? '',
                        'pickup_time' => $operation['pickup_time'] ?? null,
                        'dropoff_time' => $operation['dropoff_time'] ?? null,
                        'pickup_loc' => $operation['pickup_loc'] ?? null,
                        'dropoff_loc' => $operation['dropoff_loc'] ?? null,
                        'distance_km' => $operation['distance_km'] ?? 0.0,
                        'fare_usd' => $operation['fare_usd'] ?? 0.0,
                        'status' => $operation['status'] ?? 'pending'
                    ];
                }
            }

            $response = $this->client->bulk($params);

            $this->logger->info('Bulk rides operation completed', [
                'operations_count' => count($operations),
                'errors' => $response['errors']
            ]);

            return !$response['errors'];
        } catch (Exception $e) {
            $this->logger->error('Bulk rides operation failed', [
                'error' => $e->getMessage(),
                'operations_count' => count($operations)
            ]);
            return false;
        }
    }

    public function bulkIndexRidesFromRawData(array $rawBulkData): bool
    {
        try {
            $params = ['body' => []];

            // Process the raw bulk data (alternating between action and document)
            for ($i = 0; $i < count($rawBulkData); $i += 2) {
                if (isset($rawBulkData[$i]) && isset($rawBulkData[$i + 1])) {
                    $action = $rawBulkData[$i];
                    $document = $rawBulkData[$i + 1];

                    // Add the action
                    if (isset($action['index'])) {
                        $params['body'][] = [
                            'index' => [
                                '_index' => 'rides',
                                '_id' => $action['index']['_id'] ?? null
                            ]
                        ];
                    } elseif (isset($action['update'])) {
                        $params['body'][] = [
                            'update' => [
                                '_index' => 'rides',
                                '_id' => $action['update']['_id'] ?? null
                            ]
                        ];
                    } elseif (isset($action['delete'])) {
                        $params['body'][] = [
                            'delete' => [
                                '_index' => 'rides',
                                '_id' => $action['delete']['_id'] ?? null
                            ]
                        ];
                    }

                    // Add the document data
                    $params['body'][] = [
                        'ride_id' => $document['ride_id'] ?? '',
                        'driver_id' => $document['driver_id'] ?? '',
                        'passenger_id' => $document['passenger_id'] ?? '',
                        'pickup_time' => $document['pickup_time'] ?? null,
                        'dropoff_time' => $document['dropoff_time'] ?? null,
                        'pickup_loc' => $document['pickup_loc'] ?? null,
                        'dropoff_loc' => $document['dropoff_loc'] ?? null,
                        'distance_km' => $document['distance_km'] ?? 0.0,
                        'fare_usd' => $document['fare_usd'] ?? 0.0,
                        'status' => $document['status'] ?? 'pending'
                    ];
                }
            }

            $response = $this->client->bulk($params);

            $this->logger->info('Bulk rides from raw data completed', [
                'documents_count' => count($rawBulkData) / 2,
                'errors' => $response['errors']
            ]);

            return !$response['errors'];
        } catch (Exception $e) {
            $this->logger->error('Bulk rides from raw data failed', [
                'error' => $e->getMessage(),
                'data_count' => count($rawBulkData)
            ]);
            return false;
        }
    }
}
