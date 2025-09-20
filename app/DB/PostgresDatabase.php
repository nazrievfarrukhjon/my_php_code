<?php
namespace App\DB;

use PDO;

readonly class PostgresDatabase implements Database {
    public function __construct(private string $dsn, private string $user, private string $pass) {}

    public function connection(): PDO {
        return new PDO($this->dsn, $this->user, $this->pass);
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $table, array $data): void {
        // build insert SQL dynamically
    }

    public function update(string $table, int $id, array $data): void {}
    public function delete(string $table, int $id): void {}
}
