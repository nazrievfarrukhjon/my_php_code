<?php

namespace App\Entity;

use App\DB\Connection;
use App\DB\DB;
use App\DB\MyDB;
use App\DB\Postgresql;
use PDO;
use PDOException;

readonly class Blacklist
{
    public function __construct(private Connection $connection)
    {
    }

    public function all(): array
    {
        try {
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $query = "SELECT * FROM blacklists";
            $statement = $this->connection->prepare($query);
            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["Connection failed: " . $e->getMessage()];
        }
    }
}