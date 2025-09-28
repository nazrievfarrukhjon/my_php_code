<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

class TokenAuth implements AuthStrategy
{
    private ?array $user = null;

    public function __construct(private readonly DBConnection $db, private CacheInterface $cache)
    {
    }

    /**
     * Authenticate user by token.
     *
     * @param array $credentials ['token' => string]
     * @return bool
     * @throws Exception
     */
    public function authenticate(array $credentials): bool
    {
        if (empty($credentials['token'])) {
            throw new Exception("Token is required for authentication");
        }

        $token = $credentials['token'];

        // 1. Check cache first
        $cacheKey = $token;
        $cachedUser = $this->cache->get($cacheKey);

        if ($cachedUser) {
            $this->user = $cachedUser;
            return true;
        }

        // 2. If not in cache, fallback to DB
        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
        SELECT u.id, u.email
        FROM users u
        JOIN user_tokens t ON u.id = t.user_id
        WHERE t.token = :token AND t.expires_at > NOW()
    ";

        $stmt = $connection->prepare($query);
        $stmt->bindValue(':token', $token);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $this->user = $user;

            // 3. Store user in cache for faster future lookups
            $this->cache->set($cacheKey, $user, 3600); // cache for 1 hour
            return true;
        }

        throw new Exception("Invalid or expired token");
    }

    /**
     * Authenticate by token directly (for Bearer token)
     * @throws Exception
     */
    public function authenticateToken(string $token): bool
    {
        if ($this->cache->get($token, $token)) {
            return $this->authenticate(['token' => $token]);
        }
        throw new Exception('un auth', 401);
    }

    /**
     * Register a token for an existing user.
     *
     * @param array $credentials ['user_id' => int]
     * @return array
     * @throws Exception
     */
    public function register(array $credentials): array
    {
        if (empty($credentials['user_id'])) {
            throw new Exception("User ID is required to generate token");
        }

        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate secure random token
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

    public function getUser(): ?array
    {
        return $this->user;
    }
}
