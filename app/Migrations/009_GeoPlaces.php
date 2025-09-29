<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\Migrations\Operations\BaseMigration;
use Exception;

class GeoPlaces extends BaseMigration
{
    public function migrate(): void
    {
        if (!$this->db instanceof PostgresDatabase) {
            throw new Exception("GeoPlaces migration only supports Postgres");
        }

        $sql = "
            CREATE TABLE IF NOT EXISTS geo_places (
                id SERIAL PRIMARY KEY,
                data JSONB
            );

            CREATE INDEX IF NOT EXISTS idx_geo_places_data_gin 
            ON geo_places USING GIN(data);

            INSERT INTO geo_places (data) VALUES
            ('{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[69.2,41.3]},\"properties\":{\"name\":\"Park\"}}');
        ";

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS geo_places;");
    }
}
