<?php

namespace App\Console\Commands;

use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\MigrationQuery;
use Exception;

readonly class RollbackCommand implements Command
{
    public function __construct(
        private DBConnection $db
    ) {}

    /**
     * Rollback migrations
     * @throws Exception
     */
    public function execute(): void
    {
        (new MigrationQuery($this->db))->run('rollback');
    }
}
