<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use PDO;

class AccessTokenRepository implements RepositoryInterface
{
    private DBConnection $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }

    public function store(string $token, ?int $userId, ?string $clientId, string $scope = ''): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("
            INSERT INTO oauth_access_tokens (token, user_id, client_id, scope, created_at, expires_at)
            VALUES (:token, :user_id, :client_id, :scope, NOW(), NOW() + INTERVAL '1 hour')
        ");
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':client_id', $clientId);
        $stmt->bindValue(':scope', $scope);
        $stmt->execute();
    }

    public function find(string $token): ?array
    {
        $connection = $this->db->connection();
        $stmt = $connection->prepare("SELECT * FROM oauth_access_tokens WHERE token = :token");
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function revoke(string $token): void
    {
        $connection = $this->db->connection();
        $stmt = $connection->prepare("DELETE FROM oauth_access_tokens WHERE token = :token");
        $stmt->bindValue(':token', $token);
        $stmt->execute();
    }
}
