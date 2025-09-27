<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\Migration;
use PDO;

readonly class Blacklists implements Migration
{
    public function __construct(
        private DBConnection $db
    ) {}

    /**
     * @throws \Exception
     */
    public function migrate(): void
    {
        $conn = $this->db->connection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            $conn->exec($sql);

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
                $conn->exec($partitionSql);
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
            $conn->exec($sql);

        } else {
            throw new \Exception("Unsupported DB type");
        }
    }

    public function rollback(): void
    {
        $conn = $this->db->connection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DROP TABLE IF EXISTS blacklists CASCADE;";
        $conn->exec($sql);
    }
}
