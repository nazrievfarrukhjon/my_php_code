<?php

namespace App\Auth;

use App\DB\Contracts\DBConnection;

interface AuthStrategy {
    public function __construct(DBConnection $db);

    public function authenticate(array $credentials): bool;
    public function getUser(): ?array;
}
