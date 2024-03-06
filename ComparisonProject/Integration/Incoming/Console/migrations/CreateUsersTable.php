<?php

namespace Comparison\Integration\Incoming\Console\migrations;

use Comparison\InterfaceAdapters\Databases\Pgsql\DB;

class CreateUsersTable {

    public function up(): void
    {
        $sql = "
    CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) NOT NULL,
    password VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT current_timestamp,
    updated_at TIMESTAMP DEFAULT current_timestamp
)
";
        $pdo = DB::getPGConnection();
        $pdo->exec($sql);
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users";
        echo $sql;
        $pdo = DB::getPGConnection();
        echo $pdo->exec($sql);
    }
}
