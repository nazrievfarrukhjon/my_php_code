<?php

namespace App\DB;

use PDO;

interface DatabaseConnectionInterface
{
    public function connection(): PDO;
}