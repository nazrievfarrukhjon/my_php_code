<?php

namespace App\Auth;

interface AuthStrategy
{
    public function authenticate(array $credentials): bool;
    public function authenticateToken(string $token): bool;
    public function getUser(): ?array;
    public function register(array $request): array;
}
