<?php

namespace App\DB;

use App\Env\Env;
use Exception;

class DatabaseFactory  extends ADBFactory {
    public function createConnection(Env $env): DBConnection {
        return match ($env->get('DB_CONNECTION')) {
            'pgsql' => new PostgresDatabase(
                "pgsql:host={$env->get('DB_HOST')};dbname={$env->get('DB_NAME')}",
                $env->get('DB_USER'),
                $env->get('DB_PASS')
            ),
            'mysql' => new MysqlDatabase(
                "mysql:host={$env->get('DB_HOST')};dbname={$env->get('DB_NAME')};charset=utf8mb4",
                $env->get('DB_USER'),
                $env->get('DB_PASS')
            ),
            'sqlite' => new SqliteDatabase(
                $env->get('DB_PATH') ?? __DIR__ . '/../../database.sqlite'
            ),
            default => throw new Exception("Unsupported DB driver: {$env->get('DB_CONNECTION')}")
        };
    }
}
