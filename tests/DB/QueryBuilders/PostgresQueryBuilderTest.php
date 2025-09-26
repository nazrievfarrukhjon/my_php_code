<?php

namespace DB\QueryBuilders;


use PHPUnit\Framework\TestCase;
use App\DB\ConcreteImplementations\QueryBuilders\PostgresQueryBuilder;

class PostgresQueryBuilderTest extends TestCase
{
    public function testSelectGeneratesBasicSql()
    {
        $qb = new PostgresQueryBuilder();
        $sql = $qb->select('users', ['id', 'name'])->getSql();

        $this->assertEquals("SELECT id, name FROM users;", $sql);
    }

    public function testWhereClauseIsAppendedCorrectly()
    {
        $qb = new PostgresQueryBuilder();
        $sql = $qb->select('users', ['id'])->where('id', '=', 1)->getSql();

        $this->assertEquals("SELECT id FROM users WHERE id = 1;", $sql);
    }

    public function testWhereClauseEscapesStrings()
    {
        $qb = new PostgresQueryBuilder();
        $sql = $qb->select('users')->where('name', '=', "O'Reilly")->getSql();

        $this->assertEquals("SELECT * FROM users WHERE name = 'O\\'Reilly';", $sql);
    }
}
