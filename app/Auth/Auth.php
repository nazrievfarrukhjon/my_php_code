<?php

namespace App\Auth;

use Exception;
use PDO;

class Auth
{
    private static ?self $instance = null;
    private ?AuthStrategy $strategy = null;
    private ?array $user = null;

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function setStrategy(AuthStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * @throws Exception
     */
    public function login(array $credentials): bool
    {
        if (!$this->strategy) {
            throw new Exception("Auth strategy not set");
        }
        $success = $this->strategy->authenticate($credentials);
        if ($success) {
            $this->user = $this->strategy->getUser();
        }
        return $success;
    }

    public function user(): ?array
    {
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
}
