<?php

namespace Database\Seeders\Elasticsearch;

require_once __DIR__ . '/../../../bootstrap/bootstrap.php';

use App\Search\ElasticsearchService;
use App\Log\Logger;

class ElasticsearchSeeder
{
    public function run(): void
    {
        echo "🚀 Starting Elasticsearch seeding process...\n\n";
        
        // Run all seeders in order
        $this->runSeeder(CreateRidesIndexSeeder::class);
        $this->runSeeder(BulkIndexRidesSeeder::class);
        
        echo "\n✅ All Elasticsearch seeders completed successfully!\n";
    }
    
    private function runSeeder(string $seederClass): void
    {
        echo "Running {$seederClass}...\n";
        $seeder = new $seederClass();
        $seeder->run();
        echo "✅ {$seederClass} completed\n\n";
    }
}

// Run the seeder if called directly
if (php_sapi_name() === 'cli') {
    $seeder = new ElasticsearchSeeder();
    $seeder->run();
}
