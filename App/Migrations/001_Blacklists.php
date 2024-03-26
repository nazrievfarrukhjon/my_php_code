<?php

namespace App\Migrations;

use App\DB\MyDB;
use Exception;
use PDO;

class Blacklists implements Migration
{

    /**
     * @throws Exception
     */
    public function migrate(): void
    {
        $connection = (new MyDB())->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "
            DO $$ 
            BEGIN
                IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'blacklists') THEN
                    CREATE TABLE blacklists (
                        id BIGSERIAL PRIMARY KEY,
                        first_name VARCHAR(255),
                        second_name VARCHAR(255),
                        third_name VARCHAR(255),
                        fourth_name VARCHAR(255),
                        type VARCHAR(20),
                        birth_date DATE
                    );
                END IF;
            END $$;
        ";

        $connection->exec($query);
    }


    /**
     * @throws Exception
     */
    public function rollback(): void
    {
        $connection = (new MyDB())->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "
            DO $$ 
            BEGIN
                IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'blacklists') THEN
                    DROP TABLE IF EXISTS my_php_code_db.blacklists;
                END IF;
            END $$;
        ";
        $connection->exec($query);
    }
}