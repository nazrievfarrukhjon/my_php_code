<?php

namespace App\Auth;

use Exception;
use Firebase\JWT\Key;
use Firebase\JWT\JWT as FirebaseJWT;


class JWT
{
    private string $secret;
    private string $algo;
    private int $ttl;

    public function __construct(string $secret, string $algo = 'HS256', int $ttl = 3600)
    {
        $this->secret = $secret;
        $this->algo = $algo;
        $this->ttl = $ttl;
    }

    public function encode(array $claims): string
    {
        $now = time();
        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + $this->ttl,
        ], $claims);

        return FirebaseJWT::encode($payload, $this->secret, $this->algo);
    }

    public function decode(string $jwt): array
    {
        try {
            $decoded = FirebaseJWT::decode($jwt, new Key($this->secret, $this->algo));
            return (array)$decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage(), 401);
        }
    }
}
