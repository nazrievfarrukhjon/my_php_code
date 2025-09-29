<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

class TokenAuth implements AuthStrategy
{
    private ?array $user = null;

    public function __construct(private readonly DBConnection $db, private readonly CacheInterface $cache)
    {
    }

    /**
     * @throws Exception
     */
    public function authenticate(array $credentials): bool
    {
        if (empty($credentials['token'])) {
            throw new Exception("Token is required");
        }

        $token = $credentials['token'];
        $cachedUser = $this->cache->get($token);

        if ($cachedUser) {
            $this->user = $cachedUser;
            return true;
        }

        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $connection->prepare("
            SELECT u.id, u.email
            FROM users u
            JOIN user_tokens t ON u.id = t.user_id
            WHERE t.token = :token AND t.expires_at > NOW()
        ");
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $this->user = $user;
            $this->cache->set($token, $user, 3600);
            return true;
        }

        throw new Exception("Invalid or expired token");
    }

    /**
     * @throws Exception
     */
    public function authenticateToken(string $token): bool
    {
        return $this->authenticate(['token' => $token]);
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function register(array $credentials): array
    {
        if (empty($credentials['user_id'])) {
            throw new Exception("User ID is required to generate token");
        }

        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $token = bin2hex(random_bytes(32));

        $insert = $connection->prepare("
            INSERT INTO user_tokens (user_id, token, created_at, expires_at)
            VALUES (:user_id, :token, NOW(), NOW() + INTERVAL '30 days')
        ");
        $insert->bindValue(':user_id', $credentials['user_id']);
        $insert->bindValue(':token', $token);
        $insert->execute();

        return [
            'success' => true,
            'token' => $token
        ];
    }
}
