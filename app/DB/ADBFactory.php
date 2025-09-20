<?php

namespace App\DB;

use App\Env\Env;

abstract class ADBFactory {
    abstract public function createConnection(Env $env): DBConnection;
}