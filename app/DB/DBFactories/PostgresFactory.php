<?php

namespace App\DB\DBFactories;

use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\QueryBuilders\PostgresQueryBuilder;
use App\DB\Contracts\DBAbstractFactory;
use App\DB\Contracts\DBConnection;
use App\DB\Contracts\QueryBuilder;
use App\Env\Env;

final readonly class PostgresFactory implements DBAbstractFactory
{
    public function __construct(private Env $env, private string $role = 'primary') {}

    public function createConnection(): DBConnection
    {
        if ($this->role === 'primary') {
            $host = $this->env->get('DB_HOST_PRIMARY');
            $port = $this->env->get('DB_PORT_PRIMARY');
        } else {
            $host = $this->env->get('DB_HOST_REPLICA');
            $port = $this->env->get('DB_PORT_REPLICA');
        }

        $dsn = "pgsql:host={$host};port={$port};dbname={$this->env->get('DB_NAME')}";


        return new PostgresDatabase(
            $dsn,
            $this->env->get('DB_USER'),
            $this->env->get('DB_PASS')
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new PostgresQueryBuilder();
    }
}
