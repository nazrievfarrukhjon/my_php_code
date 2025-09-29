<?php

namespace App\Migrations;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\BaseMigration;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class UserTokens extends BaseMigration
{
    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        if ($this->db instanceof PostgresDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS user_tokens (
                    token VARCHAR(255) PRIMARY KEY,
                    user_id BIGINT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL
                );
            ";
        } elseif ($this->db instanceof SqliteDatabase) {
            $sql = "
                CREATE TABLE IF NOT EXISTS user_tokens (
                    token TEXT PRIMARY KEY,
                    user_id INTEGER NOT NULL,
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
        $this->connection->exec("DROP TABLE IF EXISTS user_tokens;");
    }
}
