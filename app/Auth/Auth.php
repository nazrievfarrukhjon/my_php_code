<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use Exception;

class Auth
{
    private static ?self $instance = null;
    private ?AuthStrategy $strategy = null;
    private ?array $user = null;

    private function __construct(private readonly CacheInterface $cache)
    {
    }

    public static function getInstance(CacheInterface $cache): self
    {
        return self::$instance ??= new self($cache);
    }

    public function setStrategy(AuthStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws Exception
     */
    public function login(array $credentials): string
    {
        if (!$this->strategy) {
            throw new Exception("Auth strategy not set");
        }

        $token = $this->generateToken();

        $success = $this->strategy->authenticate($credentials);
        if (!$success) {
            throw new Exception("Invalid credentials");
        }

        $this->user = $this->strategy->getUser();


        $this->cache->set($token, $this->user, 3600);

        return $token;
    }

    public function user(): ?array
    {
        if ($this->user) {
            unset($this->user['password']);
        }
        return $this->user;
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function register(array $request): array
    {
        try {
            if (!$this->strategy) {
                throw new Exception("Auth strategy not set");
            }
            return $this->strategy->register($request);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * @throws Exception
     */
    public function authenticateBearer(string $header): bool
    {
        if (!$this->strategy) {
            throw new Exception("Auth strategy not set");
        }

        if (!str_starts_with($header, 'Bearer ')) {
            return false;
        }

        $token = substr($header, 7);
        $success = $this->strategy->authenticateToken($token);

        if ($success) {
            $this->user = $this->strategy->getUser();
        }

        return $success;
    }

    /**
     * @throws Exception
     */
    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
