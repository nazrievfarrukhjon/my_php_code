<?php

namespace Database\Seeders\Elasticsearch;

require_once __DIR__ . '/../../../bootstrap/bootstrap.php';

use App\Search\ElasticsearchService;
use App\Log\Logger;

class GeoDistanceSearchDemo
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
            
            echo "🔍 Geo-Distance Search Demo\n";
            echo "Elasticsearch: {$host}:{$port}\n\n";
            
            // Ensure we have some data first
            echo "Ensuring rides data exists...\n";
            $elasticsearch->createRidesIndex();
            
            // Check if we have data, if not, seed some
            $searchResults = $elasticsearch->searchRides(['size' => 1]);
            if ($searchResults['total'] === 0) {
                echo "No rides found, seeding sample data...\n";
                $bulkSeeder = new BulkIndexRidesSeeder();
                $bulkSeeder->run();
            }
            
            echo "\n" . str_repeat("=", 50) . "\n";
            echo "GEO-DISTANCE SEARCH EXAMPLES\n";
            echo str_repeat("=", 50) . "\n\n";
            
            // Example 1: Search within 5km of pickup location (40.7306, -73.9352)
            echo "1. Searching for rides within 5km of pickup location (40.7306, -73.9352):\n";
            $searchResults = $elasticsearch->searchRides([
                'pickup_location' => ['lat' => 40.7306, 'lon' => -73.9352],
                'distance_km' => 5,
                'size' => 10
            ]);
            
            echo "   Found " . $searchResults['total'] . " rides within 5km\n";
            foreach ($searchResults['results'] as $i => $result) {
                $ride = $result['source'];
                echo "   " . ($i + 1) . ". Ride {$ride['ride_id']}: Driver {$ride['driver_id']}, Status: {$ride['status']}\n";
                echo "      Pickup: ({$ride['pickup_loc']['lat']}, {$ride['pickup_loc']['lon']})\n";
                echo "      Distance: {$result['score']} (relevance score)\n";
            }
            
            echo "\n" . str_repeat("-", 50) . "\n\n";
            
            // Example 2: Search within 10km of dropoff location (40.7128, -74.0060)
            echo "2. Searching for rides within 10km of dropoff location (40.7128, -74.0060):\n";
            $searchResults = $elasticsearch->searchRides([
                'dropoff_location' => ['lat' => 40.7128, 'lon' => -74.0060],
                'distance_km' => 10,
                'size' => 10
            ]);
            
            echo "   Found " . $searchResults['total'] . " rides within 10km\n";
            foreach ($searchResults['results'] as $i => $result) {
                $ride = $result['source'];
                echo "   " . ($i + 1) . ". Ride {$ride['ride_id']}: Driver {$ride['driver_id']}, Status: {$ride['status']}\n";
                echo "      Dropoff: ({$ride['dropoff_loc']['lat']}, {$ride['dropoff_loc']['lon']})\n";
                echo "      Distance: {$result['score']} (relevance score)\n";
            }
            
            echo "\n" . str_repeat("-", 50) . "\n\n";
            
            // Example 3: Search within 2km of both pickup and dropoff
            echo "3. Searching for rides within 2km of pickup location (40.7500, -73.9800):\n";
            $searchResults = $elasticsearch->searchRides([
                'pickup_location' => ['lat' => 40.7500, 'lon' => -73.9800],
                'distance_km' => 2,
                'size' => 10
            ]);
            
            echo "   Found " . $searchResults['total'] . " rides within 2km\n";
            foreach ($searchResults['results'] as $i => $result) {
                $ride = $result['source'];
                echo "   " . ($i + 1) . ". Ride {$ride['ride_id']}: Driver {$ride['driver_id']}, Status: {$ride['status']}\n";
                echo "      Pickup: ({$ride['pickup_loc']['lat']}, {$ride['pickup_loc']['lon']})\n";
                echo "      Distance: {$result['score']} (relevance score)\n";
            }
            
            echo "\n" . str_repeat("=", 50) . "\n";
            echo "✅ Geo-distance search demo completed!\n";
            echo str_repeat("=", 50) . "\n";
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

// Run the demo if called directly
if (php_sapi_name() === 'cli') {
    $demo = new GeoDistanceSearchDemo();
    $demo->run();
}
