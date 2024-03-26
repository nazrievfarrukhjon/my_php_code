<?php

namespace App\EntryPoint\Console;
use App\Migrations\Operations\MigrationFilesSqlQuery;

class Console
{
    public function __construct(
        private string $commandName,
        private string $argOne,
        private string $argTwo
    )
    {
    }


    public function handleCliCommand(): void
    {
        if ($this->commandName === 'migrate' && $this->argOne === 'absent') {
            (new MigrationFilesSqlQuery())->query('migrate');
        } elseif ($this->commandName === 'migration' && $this->argOne === 'rollback') {
            (new MigrationFilesSqlQuery())->query('rollback');
        }
    }

    public function response(): string
    {
        return 'done';
    }
}