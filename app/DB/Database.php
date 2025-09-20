<?php

namespace App\DB;

interface Database {
    public function connection(): \PDO;
    public function query(string $sql, array $params = []): array;
    public function insert(string $table, array $data): void;
    public function update(string $table, int $id, array $data): void;
    public function delete(string $table, int $id): void;
}
