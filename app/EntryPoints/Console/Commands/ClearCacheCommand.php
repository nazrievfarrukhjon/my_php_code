<?php

namespace App\EntryPoints\Console\Commands;

use App\Cache\CacheInterface;

readonly class ClearCacheCommand implements Command
{
    public function __construct(private CacheInterface $cache)
    {
    }

    public function execute(): void
    {
        $this->cache->delete('routes');
    }
}
