<?php

namespace Comparison\InterfaceAdapters\Databases\Pgsql;

use PDO;

class DB
{
    private static ?DB $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $host = env('DB_HOST');
        $port = env('DB_PORT');
        $dbname = env('DB_NAME');
        $user = env('DB_USER');
        $password = env('DB_PASSWORD');

            $connectionString = "pgsql:host=$host;dbname=$dbname;port=$port";
            $this->pdo = new PDO($connectionString, $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }

    public static function getPGInstance(): DB
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getPGConnection(): PDO
    {
        $pgInstance = DB::getPGInstance();

        return $pgInstance->pdo;
    }
}
