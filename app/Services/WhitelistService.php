<?php

namespace App\Services;

use App\Repositories\WhitelistRepository;

readonly class WhitelistService {
    public function __construct(private WhitelistRepository $repo) {}

    public function getAll(): array {
        return $this->repo->all();
    }
}