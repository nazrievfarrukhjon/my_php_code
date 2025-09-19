<?php

namespace App\DB;

use App\Env\Env;
use Exception;

class MyDB implements Connection
{
    private string $dbType = 'pgsql';

    public function __construct(private readonly Env $env)
    {
    }

    /**
     * @throws Exception
     */
    public function connection(): Connection
    {
        if ($this->env->get('DB_TYPE') === 'postgresql') {
            return $this->postgresql();
        }
        throw new Exception('no other db allowed');
    }

    public function postgresql(): Postgresql
    {
        $host = $this->env->get('DB_HOST');
        $dbname = $this->env->get('DB_NAME');
        $username = $this->env->get('DB_USER');
        $password = $this->env->get('DB_PASS');

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
