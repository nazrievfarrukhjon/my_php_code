<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

class EmailAuth implements AuthStrategy {
    private ?array $user = null;

    public function __construct(private readonly DBConnection $db, private CacheInterface $cache)
    {
    }

    /**
     * @throws Exception
     */
    public function authenticate(array $credentials): bool {
        try {
            $connection = $this->db->connection();
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query = "SELECT * FROM users WHERE email = :email";
            $statement = $connection->prepare($query);
            $statement->bindValue(':email', $credentials['email']);
            $statement->execute();

            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if ($user && isset($user['password'])) {
                if (password_verify($credentials['password'], $user['password'])) {
                    $this->user = $user;
                    return true;
                }
            }

            throw new Exception("Invalid email or password");

        } catch (Exception $e) {
            throw new Exception("Authentication failed: " . $e->getMessage());
        }
    }

    public function getUser(): ?array {
        return $this->user;
    }

    /**
     * @throws Exception
     */
    public function register(array $credentials): array
    {
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $check = $connection->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindValue(':email', $credentials['email']);
        $check->execute();
        if ($check->fetch()) {
            throw new Exception("Email already registered");
        }

        $hashedPassword = password_hash($credentials['password'], PASSWORD_BCRYPT);

        // Insert new user
        $insert = $connection->prepare("
                INSERT INTO users (email, password, created_at)
                VALUES (:email, :password, NOW())
            ");
        $insert->bindValue(':email', $credentials['email']);
        $insert->bindValue(':password', $hashedPassword);
        $insert->execute();

        return [
            'success' => true,
            'user_id' => $connection->lastInsertId()
        ];
    }

}