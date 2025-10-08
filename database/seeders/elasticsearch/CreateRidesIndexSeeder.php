<?php

namespace Database\Seeders\Elasticsearch;

require_once __DIR__ . '/../../../bootstrap/bootstrap.php';

use App\Search\ElasticsearchService;
use App\Log\Logger;

class CreateRidesIndexSeeder
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
            
            echo "Creating rides index...\n";
            echo "Elasticsearch: {$host}:{$port}\n";
            
            $success = $elasticsearch->createRidesIndex();
            
            if ($success) {
                echo "✅ Rides index created successfully!\n";
                echo "Index name: rides\n";
                echo "Mappings:\n";
                echo "  - ride_id: keyword\n";
                echo "  - driver_id: keyword\n";
                echo "  - passenger_id: keyword\n";
                echo "  - pickup_time: date\n";
                echo "  - dropoff_time: date\n";
                echo "  - pickup_loc: geo_point\n";
                echo "  - dropoff_loc: geo_point\n";
                echo "  - distance_km: float\n";
                echo "  - fare_usd: float\n";
                echo "  - status: keyword\n";
            } else {
                echo "❌ Failed to create rides index\n";
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
    $seeder = new CreateRidesIndexSeeder();
    $seeder->run();
}
