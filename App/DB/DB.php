<?php

namespace App\DB;

use Exception;

class DB implements Connection
{
    private string $dbType = 'pgsql';

    /**
     * @throws Exception
     */
    public function connection(): Connection
    {
        if ($this->dbType === 'pgsql') {
            return $this->postgresql();
        }
        throw new Exception('no other db allowed');
    }

    public function postgresql(): Postgresql
    {
        $host = 'localhost';
        $dbname = 'comparison_db';
        $username = 'postgresql';
        $password = 'postgresql';

        return new Postgresql(
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