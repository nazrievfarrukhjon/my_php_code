<?php

namespace App\Auth;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;

interface AuthStrategy {
    public function __construct(DBConnection $db, CacheInterface $cache);

    public function authenticate(array $credentials): bool;
    public function getUser(): ?array;
}
