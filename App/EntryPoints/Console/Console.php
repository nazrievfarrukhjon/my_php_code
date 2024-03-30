<?php

namespace App\EntryPoints\Console;
use App\Cache\Cache;
use App\Migrations\Operations\MigrationFilesSqlQuery;

// make cli args="arg1 arg2"
class Console
{
    public function __construct(
        private readonly string $commandName,
        private readonly string $argOne,
        private string          $argTwo
    )
    {
    }


    public function handleCliCommand(): void
    {
        if ($this->commandName === 'migrate' && $this->argOne === 'absent') {
            (new MigrationFilesSqlQuery())->query('migrate');
        } elseif ($this->commandName === 'migration' && $this->argOne === 'rollback') {
            (new MigrationFilesSqlQuery())->query('rollback');
        } elseif ($this->commandName === 'cache' && $this->argOne === 'clean') {
            (new Cache())->cleanEndpoints();
        }else {
            dd('no command match!');
        }
    }

    public function response(): string
    {
        return 'done';
    }
}