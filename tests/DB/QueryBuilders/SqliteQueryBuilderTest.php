<?php

namespace DB\QueryBuilders;

use PHPUnit\Framework\TestCase;
use App\DB\ConcreteImplementations\QueryBuilders\SqliteQueryBuilder;

class SqliteQueryBuilderTest extends TestCase
{
    public function testSelectWithDefaultColumns()
    {
        $qb = new SqliteQueryBuilder();
        $sql = $qb->select('lessons')->getSql();

        $this->assertEquals("SELECT * FROM lessons;", $sql);
    }

    public function testSelectWithCustomColumns()
    {
        $qb = new SqliteQueryBuilder();
        $sql = $qb->select('students', ['id', 'email'])->getSql();

        $this->assertEquals("SELECT id, email FROM students;", $sql);
    }

    public function testWhereAppendsCorrectly()
    {
        $qb = new SqliteQueryBuilder();
        $sql = $qb->select('students')->where('age', '>', 18)->getSql();

        $this->assertEquals("SELECT * FROM students WHERE age > 18;", $sql);
    }
}
