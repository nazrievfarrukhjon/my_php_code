<?php

namespace Comparison\Integration\Incoming\Console\migrations;


use Comparison\InterfaceAdapters\Databases\Pgsql\DB;

class CreateMigrationsTable
{

    public function up(): void
    {
        $sql = "
    CREATE TABLE IF NOT EXISTS migrations (
        id SERIAL PRIMARY KEY,
        name VARCHAR(200) not null,
        others varchar(255),
        created_at TIMESTAMP DEFAULT current_timestamp,
        updated_at TIMESTAMP DEFAULT current_timestamp
    )";
        $pdo = DB::getPGConnection();
        $pdo->exec($sql);
    }
}
