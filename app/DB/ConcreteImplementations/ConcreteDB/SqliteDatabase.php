<?php

namespace App\DB\ConcreteImplementations\ConcreteDB;

use App\DB\Contracts\DBConnection;
use PDO;

readonly class SqliteDatabase implements DBConnection {
    public function __construct(private string $path) {}

    public function connection(): PDO {
        return new PDO("sqlite:" . $this->path);
    }
}
