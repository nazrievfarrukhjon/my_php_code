<?php

namespace App\EntryPoints\Console;
use App\Cache\Cache;
use App\DB\Database;
use App\Migrations\Operations\MigrationFilesSqlQuery;
use Exception;

// make cli args="arg1 arg2"
class Console
{
    public function __construct(
        private readonly string                      $commandName,
        private readonly string                      $argOne,
        private string                               $argTwo,
        private readonly Database $db,
    )
    {
    }


    /**
     * @throws Exception
     */
    public function handleCliCommand(): void
    {
        if ($this->commandName === 'migrate' && $this->argOne === 'absent') {
            (new MigrationFilesSqlQuery($this->db))->query('migrate');
        } elseif ($this->commandName === 'migration' && $this->argOne === 'rollback') {
            (new MigrationFilesSqlQuery($this->db))->query('rollback');
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