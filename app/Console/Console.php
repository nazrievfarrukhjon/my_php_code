<?php

namespace App\Console;

use Exception;

readonly class Console
{
    public function __construct(
        private array $commands
    ) {}

    /**
     * @throws Exception
     */
    public function handle(string $commandName): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new Exception("Unknown command: $commandName");
        }

        $this->commands[$commandName]->execute();
    }
}
