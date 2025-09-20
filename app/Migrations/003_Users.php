<?php

namespace App\Migrations;

use App\DB\Database;
use App\DB\PostgresDatabase;
use App\DB\SqliteDatabase;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class Users implements Migration
{
    public function __construct(
        private Database $db
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
                CREATE TABLE IF NOT EXISTS users (
                    id BIGSERIAL PRIMARY KEY,
                    name VARCHAR(255),
                    email VARCHAR(255),
                    password VARCHAR(255)
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT,
                    email TEXT,
                    password TEXT
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

        $sql = "DROP TABLE IF EXISTS users;";
        $connection->exec($sql);
    }
}
