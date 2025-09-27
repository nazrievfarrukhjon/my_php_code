<?php

namespace App\Console\Commands;

use App\DB\Contracts\DBConnection;
use App\Faker\BlacklistFaker;

readonly class FakeBlacklistCommand implements Command
{
    public function __construct(private DBConnection $db)
    {
    }

    public function execute(): void
    {
        $faker = new BlacklistFaker($this->db, 100_000_000);
        $faker->run();
    }
}
