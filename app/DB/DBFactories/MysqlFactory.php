<?php

namespace App\DB\DBFactories;

use App\DB\ConcreteImplementations\ConcreteDB\MysqlDatabase;
use App\DB\ConcreteImplementations\QueryBuilders\MysqlQueryBuilder;
use App\DB\Contracts\DBAbstractFactory;
use App\DB\Contracts\DBConnection;
use App\DB\Contracts\QueryBuilder;
use App\Env\Env;

final readonly class MysqlFactory implements DBAbstractFactory
{
    public function __construct(private Env $env) {}

    public function createConnection(): DBConnection
    {
        return new MysqlDatabase(
            "mysql:host={$this->env->get('DB_HOST')};dbname={$this->env->get('DB_NAME')};charset=utf8mb4",
            $this->env->get('DB_USER'),
            $this->env->get('DB_PASS')
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new MysqlQueryBuilder();
    }
}
