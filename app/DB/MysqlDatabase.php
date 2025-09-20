<?php

namespace App\DB;

use PDO;

class MysqlDatabase implements DBConnection {
    public function __construct(
        private string $dsn,
        private string $user,
        private string $pass
    ) {}

    public function connection(): PDO {
        return new PDO($this->dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function insert(string $table, array $data): void {
        $fields = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";

        $stmt = $this->connection()->prepare($sql);
        $stmt->execute(array_values($data));
    }

    public function update(string $table, int $id, array $data): void {
        $fields = implode('=?, ', array_keys($data)) . '=?';
        $sql = "UPDATE {$table} SET {$fields} WHERE id=?";
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute([...array_values($data), $id]);
    }

    public function delete(string $table, int $id): void {
        $sql = "DELETE FROM {$table} WHERE id=?";
        $stmt = $this->connection()->prepare($sql);
        $stmt->execute([$id]);
    }
}
