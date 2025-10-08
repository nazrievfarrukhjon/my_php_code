<?php

require_once 'bootstrap/bootstrap.php';

use App\Search\ElasticsearchService;
use App\Log\Logger;

echo "🧪 Testing Elasticsearch API Endpoints\n";
echo str_repeat("=", 50) . "\n\n";

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
    
    echo "1. Creating rides index...\n";
    $success = $elasticsearch->createRidesIndex();
    if ($success) {
        echo "   ✅ Rides index created successfully\n";
    } else {
        echo "   ❌ Failed to create rides index\n";
    }
    
    echo "\n2. Indexing sample ride document...\n";
    $rideData = [
        'ride_id' => 'test_ride_001',
        'driver_id' => 'test_driver_123',
        'passenger_id' => 'test_passenger_456',
        'pickup_time' => '2025-01-15T10:30:00Z',
        'dropoff_time' => '2025-01-15T11:00:00Z',
        'pickup_loc' => ['lat' => 40.7128, 'lon' => -74.0060],
        'dropoff_loc' => ['lat' => 40.7589, 'lon' => -73.9851],
        'distance_km' => 5.2,
        'fare_usd' => 12.50,
        'status' => 'completed'
    ];
    
    $success = $elasticsearch->indexRideDocument($rideData);
    if ($success) {
        echo "   ✅ Ride document indexed successfully\n";
    } else {
        echo "   ❌ Failed to index ride document\n";
    }
    
    echo "\n3. Searching rides...\n";
    $searchResults = $elasticsearch->searchRides(['size' => 5]);
    echo "   Found " . $searchResults['total'] . " rides\n";
    
    if ($searchResults['total'] > 0) {
        echo "   Sample results:\n";
        foreach (array_slice($searchResults['results'], 0, 3) as $i => $result) {
            $ride = $result['source'];
            echo "     " . ($i + 1) . ". Ride ID: {$ride['ride_id']}, Driver: {$ride['driver_id']}, Status: {$ride['status']}\n";
        }
    }
    
    echo "\n4. Testing geo-distance search...\n";
    $geoSearchResults = $elasticsearch->searchRides([
        'pickup_location' => ['lat' => 40.7128, 'lon' => -74.0060],
        'distance_km' => 10,
        'size' => 5
    ]);
    echo "   Found " . $geoSearchResults['total'] . " rides within 10km of pickup location\n";
    
    echo "\n5. Getting index statistics...\n";
    $stats = $elasticsearch->getIndexStats();
    if (isset($stats['error'])) {
        echo "   ❌ Failed to get index stats: " . $stats['error'] . "\n";
    } else {
        echo "   ✅ Index: {$stats['index']}\n";
        echo "   ✅ Document count: {$stats['document_count']}\n";
        echo "   ✅ Size in bytes: {$stats['size_in_bytes']}\n";
        echo "   ✅ Status: {$stats['status']}\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ All tests completed successfully!\n";
    echo "\n🌐 You can now access the Swagger UI at: http://localhost:8080/api/docs\n";
    echo "📋 Or view the static HTML at: http://localhost:8080/swagger.html\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
    exit(1);
}
