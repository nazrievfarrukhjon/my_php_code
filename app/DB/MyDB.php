<?php

namespace App\DB;

use Exception;

class MyDB implements Connection
{
    private string $dbType = 'pgsql';

    /**
     * @throws Exception
     */
    public function connection(): Connection
    {
        if (env('DB_TYPE') === 'postgresql') {
            return $this->postgresql();
        }
        throw new Exception('no other db allowed');
    }

    public function postgresql(): Postgresql
    {
        $host = env('DB_HOST');
        $dbname = env('DB_NAME');
        $username = env('DB_USER');
        $password = env('DB_PASS');

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