<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\BaseMigration;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class Users extends BaseMigration
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        // Polymorphic SQL depending on DB type
        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id BIGSERIAL PRIMARY KEY,
                    name VARCHAR(255),
                    email VARCHAR(255),
                    password VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT,
                    email TEXT,
                    password TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
        } else {
            throw new Exception("Unsupported DB type");
        }

        $this->connection->exec($sql);
    }

    /**
     * @throws Exception
     */
    public function rollback(): void
    {
        $sql = "DROP TABLE IF EXISTS users;";
        $this->connection->exec($sql);
    }
}
