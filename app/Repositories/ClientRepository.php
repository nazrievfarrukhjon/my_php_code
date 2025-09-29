<?php

namespace App\Repositories;

use App\DB\Contracts\DBConnection;
use PDO;

class ClientRepository implements RepositoryInterface
{
    private DBConnection $db;

    public function __construct(DBConnection $db)
    {
        $this->db = $db;
    }

    public function find(string $clientId): ?array
    {
        $connection = $this->db->connection();
        $stmt = $connection->prepare("SELECT * FROM oauth_clients WHERE client_id = :client_id");
        $stmt->bindValue(':client_id', $clientId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function store(string $clientId, string $clientSecret, string $redirectUri = ''): void
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("
            INSERT INTO oauth_clients (client_id, client_secret, redirect_uri, created_at)
            VALUES (:client_id, :client_secret, :redirect_uri, NOW())
        ");
        $stmt->bindValue(':client_id', $clientId);
        $stmt->bindValue(':client_secret', password_hash($clientSecret, PASSWORD_BCRYPT));
        $stmt->bindValue(':redirect_uri', $redirectUri);
        $stmt->execute();
    }
}
