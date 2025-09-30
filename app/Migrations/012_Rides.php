<?php

namespace App\Migrations;

use App\Migrations\Operations\BaseMigration;
use Exception;

class Rides extends BaseMigration
{
    public function migrate(): void
    {
        $sql = "
            CREATE EXTENSION IF NOT EXISTS postgis;

            CREATE TABLE rides (
                id SERIAL PRIMARY KEY,
                user_id INT REFERENCES users(id) ON DELETE SET NULL,
                driver_id INT REFERENCES drivers(id) ON DELETE SET NULL,
                pickup GEOGRAPHY(Point, 4326) NOT NULL,
                dropoff GEOGRAPHY(Point, 4326) NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending'
                    CHECK (status IN ('pending','accepted','rejected','in_progress','completed','canceled')),
                fare_amount NUMERIC(10,2) DEFAULT NULL,
                payment_method TEXT DEFAULT NULL,
                requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );

            CREATE INDEX idx_rides_pickup ON rides USING GIST(pickup);
            CREATE INDEX idx_rides_dropoff ON rides USING GIST(dropoff);
        ";

        $this->connection->exec($sql);
    }

    public function rollback()
    {
        $this->connection->exec("DROP TABLE IF EXISTS rides;");
    }
}
