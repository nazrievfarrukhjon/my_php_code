<?php

namespace App\DB;

interface Connection
{
    public function getById();
    public function fetchAll();

    public function store();

}