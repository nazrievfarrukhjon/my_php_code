<?php

namespace App\Services;

class WhitelistService {
    public function __construct(private WhitelistRepositoryInterface $repo) {}

    public function getAll(): array {
        return $repo->all();
    }
}