<?php

namespace App\DB;

use PDO;

interface DBConnection {
    public function connection(): PDO;

}