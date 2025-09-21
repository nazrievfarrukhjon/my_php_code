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
    public function __construct(private Env $env) {}

    public function createConnection(): DBConnection
    {
        return new PostgresDatabase(
            "pgsql:host={$this->env->get('DB_HOST')};dbname={$this->env->get('DB_NAME')}",
            $this->env->get('DB_USER'),
            $this->env->get('DB_PASS')
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new PostgresQueryBuilder();
    }
}
