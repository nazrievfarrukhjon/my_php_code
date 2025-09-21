<?php

namespace App\DB\Contracts;

interface DBAbstractFactory {
    public function createConnection(): DBConnection;
    public function createQueryBuilder(): QueryBuilder;
}
