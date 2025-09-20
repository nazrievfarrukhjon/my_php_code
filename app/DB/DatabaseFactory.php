<?php


namespace App\DB;

use App\Env\Env;
use Exception;

class DatabaseFactory {

    /**
     * @throws Exception
     */
    public static function create(Env $env): Database {
        return match ($env->get('DB_CONNECTION')) {
            'pgsql' => new PostgresDatabase(
                "pgsql:host={$env->get('DB_HOST')};dbname={$env->get('DB_NAME')}",
                $env->get('DB_USER'),
                $env->get('DB_PASS')
            ),
            'sqlite' => new SqliteDatabase($env->get('DB_PATH') ?? __DIR__ . '/../../database.sqlite'),
            default => throw new Exception("Unsupported DB driver")
        };
    }
}