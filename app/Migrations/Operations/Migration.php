<?php

namespace App\Migrations\Operations;

interface Migration
{
    public function migrate();

    public function rollback();
}