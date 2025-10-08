<?php

namespace Database\Seeders\Elasticsearch;

require_once __DIR__ . '/../../../bootstrap/bootstrap.php';

use App\Search\ElasticsearchService;
use App\Log\Logger;

class BulkIndexRidesSeeder
{
    public function run(): void
    {
        try {
            $logger = Logger::getInstance();
            
            // Get Elasticsearch configuration from environment
            $host = $_ENV['ELASTICSEARCH_HOST'] ?? 'localhost';
            $port = (int) ($_ENV['ELASTICSEARCH_PORT'] ?? 9200);
            
            $elasticsearch = new ElasticsearchService(
                host: $host,
                port: $port,
                index: 'fraud', // This is for the fraud index, rides will use 'rides'
                logger: $logger
            );
            
            echo "Bulk indexing rides...\n";
            echo "Elasticsearch: {$host}:{$port}\n";
            
            // First, ensure the rides index exists
            echo "Creating rides index if it doesn't exist...\n";
            $elasticsearch->createRidesIndex();
            
            // Define the bulk operations exactly as you specified (alternating action and document)
            $rawBulkData = [
                // First ride
                ['index' => []],
                [
                    'ride_id' => 'r1',
                    'driver_id' => 'd1',
                    'passenger_id' => 'p1',
                    'pickup_time' => '2025-10-08T08:00:00Z',
                    'dropoff_time' => '2025-10-08T08:20:00Z',
                    'pickup_loc' => ['lat' => 40.7128, 'lon' => -74.0060],
                    'dropoff_loc' => ['lat' => 40.7306, 'lon' => -73.9352],
                    'distance_km' => 12.5,
                    'fare_usd' => 15.5,
                    'status' => 'completed'
                ],
                // Second ride
                ['index' => []],
                [
                    'ride_id' => 'r2',
                    'driver_id' => 'd2',
                    'passenger_id' => 'p2',
                    'pickup_time' => '2025-10-08T09:00:00Z',
                    'dropoff_time' => '2025-10-08T09:30:00Z',
                    'pickup_loc' => ['lat' => 40.7500, 'lon' => -73.9800],
                    'dropoff_loc' => ['lat' => 40.7550, 'lon' => -73.9730],
                    'distance_km' => 5.2,
                    'fare_usd' => 8.3,
                    'status' => 'completed'
                ],
                // Third ride
                ['index' => []],
                [
                    'ride_id' => 'r3',
                    'driver_id' => 'd1',
                    'passenger_id' => 'p3',
                    'pickup_time' => '2025-10-08T10:00:00Z',
                    'dropoff_time' => '2025-10-08T10:10:00Z',
                    'pickup_loc' => ['lat' => 40.7300, 'lon' => -73.9950],
                    'dropoff_loc' => ['lat' => 40.7400, 'lon' => -73.9850],
                    'distance_km' => 3.1,
                    'fare_usd' => 5.9,
                    'status' => 'active'
                ]
            ];
            
            echo "Executing bulk operation with " . (count($rawBulkData) / 2) . " rides...\n";
            
            $success = $elasticsearch->bulkIndexRidesFromRawData($rawBulkData);
            
            if ($success) {
                echo "✅ Bulk rides operation completed successfully!\n";
                echo "Indexed rides:\n";
                echo "  - r1: Driver d1, Passenger p1, $15.50, completed\n";
                echo "  - r2: Driver d2, Passenger p2, $8.30, completed\n";
                echo "  - r3: Driver d1, Passenger p3, $5.90, active\n";
                
                // Verify the data was indexed
                echo "\nVerifying indexed data...\n";
                $searchResults = $elasticsearch->searchRides(['size' => 10]);
                echo "Total rides in index: " . $searchResults['total'] . "\n";
                
                if ($searchResults['total'] > 0) {
                    echo "Sample ride data:\n";
                    foreach (array_slice($searchResults['results'], 0, 3) as $i => $result) {
                        $ride = $result['source'];
                        echo "  " . ($i + 1) . ". Ride ID: {$ride['ride_id']}, Driver: {$ride['driver_id']}, Status: {$ride['status']}, Fare: \${$ride['fare_usd']}\n";
                    }
                }
            } else {
                echo "❌ Bulk rides operation failed\n";
                exit(1);
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

// Run the seeder if called directly
if (php_sapi_name() === 'cli') {
    $seeder = new BulkIndexRidesSeeder();
    $seeder->run();
}
