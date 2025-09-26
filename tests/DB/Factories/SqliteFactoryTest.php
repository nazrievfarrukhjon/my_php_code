<?php

namespace DB\Factories;


use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use App\DB\DBFactories\SqliteFactory;
use App\DB\ConcreteImplementations\ConcreteDB\SqliteDatabase;
use App\DB\ConcreteImplementations\QueryBuilders\SqliteQueryBuilder;
use App\Env\Env;

class SqliteFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testFactoryCreatesSqliteDatabase()
    {
        $env = $this->createMock(Env::class);
        $env->method('get')->willReturn('/tmp/test.sqlite');

        $factory = new SqliteFactory($env);
        $connection = $factory->createConnection();
        $this->assertInstanceOf(SqliteDatabase::class, $connection);

        $qb = $factory->createQueryBuilder();
        $this->assertInstanceOf(SqliteQueryBuilder::class, $qb);
    }
}
