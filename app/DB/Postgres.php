<?php

namespace App\DB;

use App\Env\Env;
use Exception;
use PDO;

readonly class Postgres implements Connection, DatabaseInterface, DatabaseConnectionInterface
{

    public function __construct(private Env $env)
    {
    }

    /**
     * @throws Exception
     */
    public function connection(): PDO
    {
        if ($this->env->get('DB_CONNECTION') === 'pgsql') {
            return $this->postgresql();
        }

        if ($this->env->get('DB_CONNECTION') === 'sqlite') {
            return $this->sqlite();
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

    private function sqlite(): PDO
    {
        // Path to SQLite database file
        $path = $this->env->get('DB_PATH') ?? __DIR__ . '/database.sqlite';

        return new PDO("sqlite:" . $path);
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

    public function query(string $sql, array $params = []): array
    {
        // TODO: Implement query() method.
    }

    public function insert(string $table, array $data): void
    {
        // TODO: Implement insert() method.
    }

    public function update(string $table, int $id, array $data): void
    {
        // TODO: Implement update() method.
    }

    public function delete(string $table, int $id): void
    {
        // TODO: Implement delete() method.
    }


}
