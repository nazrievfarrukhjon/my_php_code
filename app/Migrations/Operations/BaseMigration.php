<?php

namespace App\Migrations\Operations;

use App\DB\Contracts\DBConnection;
use PDO;

abstract class BaseMigration implements Migration {
    protected PDO $connection;

    public function __construct(protected DBConnection $db) {
        $this->connection = $this->db->connection();
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}