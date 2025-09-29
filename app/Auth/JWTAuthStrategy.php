<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use Exception;
use PDO;

class JWTAuthStrategy implements AuthStrategy
{
    private ?array $user = null;

    public function __construct(
        private readonly DBConnection $db,
        private readonly CacheInterface $cache,
        private readonly JWT $jwt,
    ) {}

    /**
     * @throws Exception
     */
    public function authenticate(array $credentials): bool
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            throw new Exception("Email and password are required");
        }

        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT * FROM users WHERE email = :email";
        $statement = $connection->prepare($query);
        $statement->bindValue(':email', $credentials['email']);
        $statement->execute();

        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$user || !isset($user['password']) || !password_verify($credentials['password'], $user['password'])) {
            throw new Exception("Invalid email or password");
        }

        $this->user = $user;
        return true;
    }

    /**
     * @throws Exception
     */
    public function authenticateToken(string $token): bool
    {
        try {
            $claims = $this->jwt->decode($token);

            if (empty($claims['sub'])) {
                throw new Exception("Token missing subject");
            }

            $cacheKey = "jwt_user_" . $claims['sub'];
            $cachedUser = $this->cache->get($cacheKey);

            if ($cachedUser) {
                $this->user = $cachedUser;
                return true;
            }

            $connection = $this->db->connection();
            $stmt = $connection->prepare("SELECT id, email FROM users WHERE id = :id");
            $stmt->bindValue(':id', $claims['sub']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new Exception("User not found for token");
            }

            $this->user = $user;
            $this->cache->set($cacheKey, $user, 3600);
            return true;
        } catch (Exception $e) {
            throw new Exception("JWT authentication failed: " . $e->getMessage(), 401);
        }
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function register(array $request): array
    {
        if (empty($request['email']) || empty($request['password'])) {
            throw new Exception("Email and password are required");
        }

        $connection = $this->db->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $check = $connection->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindValue(':email', $request['email']);
        $check->execute();
        if ($check->fetch()) {
            throw new Exception("Email already registered");
        }

        $hashedPassword = password_hash($request['password'], PASSWORD_BCRYPT);

        $insert = $connection->prepare("
            INSERT INTO users (email, password, created_at)
            VALUES (:email, :password, NOW())
        ");
        $insert->bindValue(':email', $request['email']);
        $insert->bindValue(':password', $hashedPassword);
        $insert->execute();

        $userId = $connection->lastInsertId();
        return [
            'success' => true,
            'user_id' => $userId
        ];
    }

    public function getJWT(int $userId): string
    {
        return $this->jwt->encode([
            'sub' => $userId,
            'exp' => time() + 3600
        ]);
    }

}
