<?php

namespace App\Migrations;

use App\Migrations\Operations\BaseMigration;

class BillingTransactions extends BaseMigration
{
    public function migrate(): void
    {
        $sql = "
            CREATE TABLE billing_transactions (
                id SERIAL PRIMARY KEY,
                ride_id INT REFERENCES rides(id) ON DELETE CASCADE,
                user_id INT DEFAULT NULL,
                amount NUMERIC(10,2) NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending'
                    CHECK (status IN ('pending','paid','refunded')),
                payment_method TEXT DEFAULT 'card',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ";

        $this->connection->exec($sql);
    }

    public function rollback(): void
    {
        $this->connection->exec("DROP TABLE IF EXISTS billing_transactions;");
    }
}
