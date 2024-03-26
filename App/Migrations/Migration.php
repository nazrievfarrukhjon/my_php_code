<?php

namespace App\Migrations;

interface Migration
{
    public function migrate();

    public function rollback();
}