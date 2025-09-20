<?php

namespace App\Migrations;

use App\DB\Database;
use App\DB\PostgresDatabase;
use App\DB\SqliteDatabase;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class Whitelists implements Migration
{
    public function __construct(
        private readonly Database $db
    ) {}

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Polymorphic SQL depending on DB type
        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS whitelists (
                    id BIGSERIAL PRIMARY KEY,
                    first_name VARCHAR(255),
                    second_name VARCHAR(255),
                    third_name VARCHAR(255),
                    fourth_name VARCHAR(255),
                    type VARCHAR(20),
                    birth_date DATE
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS whitelists (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    first_name TEXT,
                    second_name TEXT,
                    third_name TEXT,
                    fourth_name TEXT,
                    type TEXT,
                    birth_date TEXT
                );
            ";
        } else {
            throw new Exception("Unsupported DB type");
        }

        $connection->exec($sql);
    }

    /**
     * @throws Exception
     */
    public function rollback(): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "DROP TABLE IF EXISTS whitelists;";
        $connection->exec($sql);
    }
}
