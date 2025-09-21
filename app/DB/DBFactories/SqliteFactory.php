<?php

namespace App\DB\DBFactories;

use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\ConcreteImplementations\QueryBuilders\SqliteQueryBuilder;
use App\DB\Contracts\DBAbstractFactory;
use App\DB\Contracts\DBConnection;
use App\DB\Contracts\QueryBuilder;
use App\Env\Env;

final readonly class SqliteFactory implements DBAbstractFactory
{
    public function __construct(private Env $env) {}

    public function createConnection(): DBConnection
    {
        return new SqliteDatabase(
            $this->env->get('DB_PATH') ?? __DIR__ . '/../../../database.sqlite'
        );
    }

    public function createQueryBuilder(): QueryBuilder
    {
        return new SqliteQueryBuilder();
    }
}
