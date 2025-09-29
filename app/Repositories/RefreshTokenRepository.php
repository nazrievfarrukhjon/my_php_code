<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use PDO;

class RefreshTokenRepository implements RepositoryInterface
{
    private DBConnection $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }

    public function store(string $refreshToken, string $accessToken): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("
            INSERT INTO oauth_refresh_tokens (refresh_token, access_token, created_at, expires_at)
            VALUES (:refresh_token, :access_token, NOW(), NOW() + INTERVAL '30 days')
        ");
        $stmt->bindValue(':refresh_token', $refreshToken);
        $stmt->bindValue(':access_token', $accessToken);
        $stmt->execute();
    }

    public function find(string $refreshToken): ?array
    {
        $connection = $this->db->connection();
        $stmt = $connection->prepare("SELECT * FROM oauth_refresh_tokens WHERE refresh_token = :refresh_token");
        $stmt->bindValue(':refresh_token', $refreshToken);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function revoke(string $refreshToken): void
    {
        $connection = $this->db->connection();
        $stmt = $connection->prepare("DELETE FROM oauth_refresh_tokens WHERE refresh_token = :refresh_token");
        $stmt->bindValue(':refresh_token', $refreshToken);
        $stmt->execute();
    }
}
