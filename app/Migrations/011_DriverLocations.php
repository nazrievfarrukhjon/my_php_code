<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\Migrations\Operations\BaseMigration;
use Exception;

class DriverLocations extends BaseMigration
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        if (!$this->db instanceof PostgresDatabase) {
            throw new Exception("DriverLocations migration only supports Postgres");
        }

        $sql = "
            CREATE EXTENSION IF NOT EXISTS postgis;

            CREATE TABLE driver_locations (
                id SERIAL PRIMARY KEY,
                driver_id INT NOT NULL REFERENCES drivers(id) ON DELETE CASCADE,
                location GEOGRAPHY(Point, 4326) NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX idx_driver_locations ON driver_locations USING GIST(location);
        ";

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS driver_locations;");
    }
}
