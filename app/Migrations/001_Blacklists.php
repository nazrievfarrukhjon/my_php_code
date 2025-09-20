<?php

namespace App\Migrations;

use App\DB\DatabaseConnectionInterface;
use App\DB\Postgres;
use App\DB\Sqlite;
use App\Migrations\Operations\Migration;
use PDO;

readonly class Blacklists implements Migration
{
    public function __construct(
        private DatabaseConnectionInterface $db
    ) {}

    public function migrate(): void
    {
        $conn = $this->db->connection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->db instanceof Postgres) {
            $sql = "
                CREATE TABLE IF NOT EXISTS blacklists (
                    id BIGSERIAL PRIMARY KEY,
                    first_name VARCHAR(255),
                    second_name VARCHAR(255),
                    third_name VARCHAR(255),
                    fourth_name VARCHAR(255),
                    type VARCHAR(20),
                    birth_date DATE
                );
            ";
        } elseif ($this->db instanceof Sqlite) {
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
        } else {
            throw new \Exception("Unsupported DB type");
        }

        $conn->exec($sql);
    }

    public function rollback(): void
    {
        $conn = $this->db->connection();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DROP TABLE IF EXISTS blacklists;";
        $conn->exec($sql);
    }

}