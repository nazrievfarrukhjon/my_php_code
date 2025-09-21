<?php

namespace App\DB\ConcreteImplementations\QueryBuilders;

use App\DB\Contracts\QueryBuilder;

class MysqlQueryBuilder implements QueryBuilder
{
    private string $query = '';

    public function select(string $table, array $columns = ['*']): self
    {
        $cols = implode(', ', $columns);
        $this->query = "SELECT {$cols} FROM {$table}";
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $val = is_numeric($value) ? $value : "'$value'";
        $this->query .= " WHERE {$column} {$operator} {$val}";
        return $this;
    }

    public function getSql(): string
    {
        return $this->query . ';';
    }
}
