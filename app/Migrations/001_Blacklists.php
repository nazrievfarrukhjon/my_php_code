<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\Migrations\Operations\BaseMigration;

/**
 * # CREATE EXTENSION IF NOT EXISTS pg_trgm;
 * # CREATE INDEX blacklists_name_trgm_idx
 * # ON blacklists
 * # USING GIN (name gin_trgm_ops);
 */
class Blacklists extends BaseMigration
{
    /**
     * @throws \Exception
     */
    public function migrate(): void
    {

        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS blacklists (
                    id BIGSERIAL,
                    first_name VARCHAR(255),
                    second_name VARCHAR(255),
                    third_name VARCHAR(255),
                    fourth_name VARCHAR(255),
                    type VARCHAR(20),
                    birth_date DATE NOT NULL,
                    PRIMARY KEY (id, birth_date) -- composite PK required
                ) PARTITION BY RANGE (birth_date);
            ";
            $this->connection->exec($sql);

            // Create partitions by 10 years (1950–2029 as example)
            for ($year = 1950; $year <= 2030; $year += 10) {
                $start = "$year-01-01";
                $end = ($year + 10) . "-01-01";
                $partitionName = "blacklists_" . $year . "_" . ($year + 9);

                $partitionSql = "
                    CREATE TABLE IF NOT EXISTS {$partitionName}
                    PARTITION OF blacklists
                    FOR VALUES FROM ('$start') TO ('$end');
                ";
                $this->connection->exec($partitionSql);
            }

            //

            $names = ['first_name'];
            for ($year = 1950; $year <= 2030; $year += 10) {
                $partitionName = "blacklists_" . $year . "_" . ($year + 9);
                foreach ($names as $col) {
                    $indexName = "{$partitionName}_{$col}_trgm_idx";
                    $indexSql = "
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_class c
                    JOIN pg_namespace n ON n.oid = c.relnamespace
                    WHERE c.relname = '{$indexName}'
                ) THEN
                    CREATE INDEX {$indexName}
                    ON {$partitionName}
                    USING GIN ({$col} gin_trgm_ops);
                END IF;
            END
            $$;
        ";
                    $this->connection->exec($indexSql);
                }
            }


        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = '
                CREATE TABLE IF NOT EXISTS blacklists (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    first_name TEXT,
                    second_name TEXT,
                    third_name TEXT,
                    fourth_name TEXT,
                    type TEXT,
                    birth_date TEXT
                );
            ';
            $this->connection->exec($sql);

        } else {
            throw new \Exception("Unsupported DB type");
        }
    }

    public function rollback(): void
    {
        $sql = "DROP TABLE IF EXISTS blacklists CASCADE;";
        $this->connection->exec($sql);
    }
}
