<?php

namespace App\Faker;

use App\DB\Contracts\DBConnection;
use Faker\Factory;
use PDO;

class BlacklistFaker
{
    private int $rows;
    private int $batchSize;

    public function __construct(
        private readonly DBConnection $db,
        int $rows = 10_000_000,
        int $batchSize = 10_000
    ){
        $this->rows = $rows;
        $this->batchSize = $batchSize;
    }

    public function run(): void
    {
        $this->bulkInsert();
    }

    private function bulkInsert(): void
    {
        $faker = Factory::create();
        $pdo = $this->db->connection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $rows = [];

        for ($i = 1; $i <= $this->rows; $i++) {
            $rows[] = [
                $faker->firstName,
                $faker->lastName,
                $faker->lastName,
                $faker->lastName,
                $faker->randomElement(['A','B','C','D']),
                $faker->date('Y-m-d', '2029-12-31')
            ];

            if (count($rows) === $this->batchSize) {
                $this->insertBatch($pdo, $rows);
                echo "Inserted $i rows...\n";
                $rows = [];
            }
        }

        if (!empty($rows)) {
            $this->insertBatch($pdo, $rows);
            echo "Inserted final batch\n";
        }

        echo "All {$this->rows} rows inserted.\n";
    }

    private function insertBatch(PDO $pdo, array $rows): void
    {
        $placeholders = implode(',', array_fill(0, count($rows), '(?,?,?,?,?,?)'));
        $stmt = $pdo->prepare("INSERT INTO blacklists (first_name, second_name, third_name, fourth_name, type, birth_date) VALUES $placeholders");

        $stmt->execute(array_merge(...$rows));
    }
}
