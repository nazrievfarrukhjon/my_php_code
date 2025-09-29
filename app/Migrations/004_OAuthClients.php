<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\BaseMigration;
use Exception;
use PDO;

class OAuthClients extends BaseMigration
{
    public function migrate(): void
    {
        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_clients (
                    client_id VARCHAR(100) PRIMARY KEY,
                    client_secret VARCHAR(255) NOT NULL,
                    redirect_uri TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_clients (
                    client_id TEXT PRIMARY KEY,
                    client_secret TEXT NOT NULL,
                    redirect_uri TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
        } else {
            throw new Exception("Unsupported DB type");
        }

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS oauth_clients;");
    }
}
