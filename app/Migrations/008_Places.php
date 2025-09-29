<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\Migrations\Operations\BaseMigration;
use Exception;

class Places extends BaseMigration
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        if (!$this->db instanceof PostgresDatabase) {
            throw new Exception("Places migration only supports Postgres");
        }

        $sql = "
            CREATE EXTENSION IF NOT EXISTS postgis;

            CREATE TABLE IF NOT EXISTS places (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100),
                location GEOGRAPHY(POINT, 4326)
            );

            CREATE INDEX IF NOT EXISTS idx_places_location_gist 
            ON places USING GIST(location);
        ";

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS places;");
    }
}
