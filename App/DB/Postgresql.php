<?php

namespace App\DB;

use PDO;

class Postgresql extends PDO implements Connection
{
    public function __construct(string $dsn, ?string $username = null, ?string $password = null, ?array $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);
    }

    public function pdo(): Postgresql
    {
        $host = 'localhost';
        $dbname = 'comparison_db';
        $username = 'postgresql';
        $password = 'postgresql';

        return new self(
            "pgsql:host=$host;dbname=$dbname",
            $username,
            $password
        );
    }

    public function getById()
    {
        // TODO: Implement getById() method.
    }

    public function fetchAll()
    {
        // TODO: Implement fetchAll() method.
    }

    public function store()
    {
        // TODO: Implement store() method.
    }
}