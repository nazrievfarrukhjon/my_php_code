# Elasticsearch Seeders

This directory contains seeders for Elasticsearch indices and data.

## Available Seeders

### 1. CreateRidesIndexSeeder
Creates the `rides` index with the following mapping:
- `ride_id`: keyword
- `driver_id`: keyword  
- `passenger_id`: keyword
- `pickup_time`: date
- `dropoff_time`: date
- `pickup_loc`: geo_point
- `dropoff_loc`: geo_point
- `distance_km`: float
- `fare_usd`: float
- `status`: keyword

### 2. BulkIndexRidesSeeder
Bulk indexes sample ride data into the `rides` index.

### 3. ElasticsearchSeeder
Master seeder that runs all Elasticsearch seeders in the correct order.

### 4. GeoDistanceSearchDemo
Demonstrates geo-distance search functionality for rides.

## Usage

### Run Individual Seeders

```bash
# Create rides index
php database/seeders/elasticsearch/CreateRidesIndexSeeder.php

# Bulk index sample rides
php database/seeders/elasticsearch/BulkIndexRidesSeeder.php

# Run all seeders
php database/seeders/elasticsearch/ElasticsearchSeeder.php

# Demo geo-distance search
php database/seeders/elasticsearch/GeoDistanceSearchDemo.php
```

### Environment Variables

Set these environment variables for Elasticsearch connection:

```bash
export ELASTICSEARCH_HOST=localhost
export ELASTICSEARCH_PORT=9200
```

## Geo-Distance Search

The rides index supports geo-distance searches on both `pickup_loc` and `dropoff_loc` fields.

### Example Search Query

```php
$searchResults = $elasticsearch->searchRides([
    'pickup_location' => ['lat' => 40.7306, 'lon' => -73.9352],
    'distance_km' => 5,
    'size' => 10
]);
```

This is equivalent to the Elasticsearch query:
```json
{
  "query": {
    "geo_distance": {
      "distance": "5km",
      "pickup_loc": { "lat": 40.7306, "lon": -73.9352 }
    }
  }
}
```

## Features

- ✅ Create rides index with proper geo_point mappings
- ✅ Bulk index ride data
- ✅ Geo-distance search on pickup and dropoff locations
- ✅ Search by driver, passenger, status, date range, fare range
- ✅ Proper error handling and logging
- ✅ CLI-friendly output
