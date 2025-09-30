<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use PDO;
use Exception;

class UserRepository implements RepositoryInterface
{
    public function __construct(
        private readonly DBConnection $primaryDB,
        private readonly DBConnection $replicaDB
    ) {}

    /**
     * Get user by ID
     */
    public function getUserById(int $userId): ?array
    {
        $sql = "SELECT id, name, email, role, created_at, updated_at 
                FROM users 
                WHERE id = :user_id";

        $stmt = $this->replicaDB->connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Update user data
     */
    public function updateUser(int $userId, array $data): array
    {
        $fields = [];
        $params = ['user_id' => $userId];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[$key] = $value;
        }

        if (empty($fields)) {
            throw new Exception("No data provided for update");
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :user_id RETURNING id, name, email, role, created_at, updated_at";

        $stmt = $this->primaryDB->connection()->prepare($sql);
        $stmt->execute($params);

        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$updatedUser) {
            throw new Exception("User not found or update failed");
        }

        return $updatedUser;
    }

    /**
     * Delete user
     */
    public function deleteUser(int $userId): void
    {
        $sql = "DELETE FROM users WHERE id = :user_id";

        $stmt = $this->primaryDB->connection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("User not found or delete failed");
        }
    }
}
