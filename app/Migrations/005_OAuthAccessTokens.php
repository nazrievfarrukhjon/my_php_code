<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\BaseMigration;
use Exception;
use PDO;

class OauthAccessTokens extends BaseMigration
{
    public function migrate(): void
    {
        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_access_tokens (
                    token VARCHAR(255) PRIMARY KEY,
                    user_id BIGINT,
                    client_id VARCHAR(100),
                    scope TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS oauth_access_tokens (
                    token TEXT PRIMARY KEY,
                    user_id INTEGER,
                    client_id TEXT,
                    scope TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    expires_at DATETIME NOT NULL
                );
            ";
        } else {
            throw new Exception("Unsupported DB type");
        }

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS oauth_access_tokens;");
    }
}
