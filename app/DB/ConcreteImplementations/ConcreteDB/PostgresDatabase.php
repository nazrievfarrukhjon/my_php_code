<?php

namespace App\DB\ConcreteImplementations\ConcreteDB;

use App\DB\Contracts\DBConnection;
use PDO;

readonly class PostgresDatabase implements DBConnection {
    public function __construct(
        private string $dsn,
        private string $user,
        private string $pass
    ) {}

    public function connection(): PDO {
        return new PDO($this->dsn, $this->user, $this->pass);
    }
}
