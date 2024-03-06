<?php

namespace Comparison\Integration\Incoming\Console\migrations;

use Comparison\InterfaceAdapters\Databases\Pgsql\DB;

class CreateBlacklistedsTable {

    public function up(): void
    {
        $sql = "
CREATE TABLE IF NOT EXISTS blacklisteds (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100),
    second_name VARCHAR(100),
    third_name VARCHAR(100),
    search_index VARCHAR(300),
    birth_date DATE,
    type VARCHAR(100),
    comments VARCHAR(200),
    created_at TIMESTAMP DEFAULT current_timestamp,
    updated_at TIMESTAMP DEFAULT current_timestamp
);
";
        $pdo = DB::getPGConnection();
        $pdo->exec($sql);
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS blacklisteds";
        echo $sql;
        $pdo = DB::getPGConnection();
        echo $pdo->exec($sql);
    }
}
