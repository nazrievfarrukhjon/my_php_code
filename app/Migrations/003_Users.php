<?php

namespace App\Migrations;

use App\DB\MyDB;
use App\Migrations\Operations\Migration;
use Exception;
use PDO;

class Users implements Migration
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
                IF NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users') THEN
                    CREATE TABLE users (
                        id BIGSERIAL PRIMARY KEY,
                        name VARCHAR(255),
                        email VARCHAR(255),
                        password VARCHAR(255)
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
                IF EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users') THEN
                    DROP TABLE IF EXISTS users;
                END IF;
            END $$;
        ";
        $connection->exec($query);
    }
}