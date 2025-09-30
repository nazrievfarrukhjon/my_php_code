<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\Migrations\Operations\BaseMigration;
use Exception;

class Drivers extends BaseMigration
{

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        if (!$this->db instanceof PostgresDatabase) {
            throw new Exception("GeoPlaces migration only supports Postgres");
        }

        $sql = "
            CREATE EXTENSION IF NOT EXISTS postgis;
            CREATE TABLE IF NOT EXISTS drivers (
                id SERIAL PRIMARY KEY,
                name TEXT,
                location GEOGRAPHY(Point, 4326)
            );
            CREATE INDEX IF NOT EXISTS idx_drivers_location ON drivers USING GIST (location);
        ";

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS drivers;");
    }
}
