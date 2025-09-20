<?php

namespace App\DB;

use PDO;

readonly class SqliteDatabase implements DBConnection {
    public function __construct(private string $path) {}

    public function connection(): PDO {
        return new PDO("sqlite:" . $this->path);
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $data): void {}
    public function update(string $table, int $id, array $data): void {}
    public function delete(string $table, int $id): void {}
}
