<?php

namespace App\DB\Contracts;

use PDO;

interface DBConnection {
    public function connection(): PDO;

}