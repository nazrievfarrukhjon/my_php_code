<?php

namespace App\Console\Commands;

use App\DB\Contracts\DBConnection;
use App\Migrations\Operations\MigrationQuery;
use Exception;

readonly class MigrateCommand implements Command
{
    public function __construct(
        private DBConnection $db,
        private string  $action = 'migrate'
    ) {}

    /**
     * Run migrations
     * @throws Exception
     */
    public function execute(): void
    {
        (new MigrationQuery($this->db))->run($this->action);
    }
}
