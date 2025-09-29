<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class OAuthRefreshTokens implements Migration
{
    public function __construct(private DBConnection $db) {}

    public function migrate(): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
                    refresh_token VARCHAR(255) PRIMARY KEY,
                    access_token VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
                    refresh_token TEXT PRIMARY KEY,
                    access_token TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME NOT NULL
                );
            ";
        } else {
            throw new Exception("Unsupported DB type");
        }

        $connection->exec($sql);
    }

    public function rollback(): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connection->exec("DROP TABLE IF EXISTS oauth_refresh_tokens;");
    }
}
