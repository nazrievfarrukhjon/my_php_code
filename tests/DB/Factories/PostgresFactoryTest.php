<?php

namespace DB\Factories;

use PHPUnit\Framework\TestCase;
use App\DB\DBFactories\PostgresFactory;
use App\DB\ConcreteImplementations\ConcreteDB\PostgresDatabase;
use App\DB\ConcreteImplementations\QueryBuilders\PostgresQueryBuilder;
use App\Env\Env;

class PostgresFactoryTest extends TestCase
{
    public function testFactoryCreatesPostgresDatabase()
    {
        $env = $this->createMock(Env::class);
        $env->method('get')->willReturnMap([
            ['DB_HOST', 'localhost'],
            ['DB_NAME', 'test_db'],
            ['DB_USER', 'test_user'],
            ['DB_PASS', 'secret'],
        ]);

        $factory = new PostgresFactory($env);
        $connection = $factory->createConnection();
        $this->assertInstanceOf(PostgresDatabase::class, $connection);

        $qb = $factory->createQueryBuilder();
        $this->assertInstanceOf(PostgresQueryBuilder::class, $qb);
    }
}
