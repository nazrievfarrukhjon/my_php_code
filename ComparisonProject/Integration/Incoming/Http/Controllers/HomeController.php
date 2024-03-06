<?php

namespace App\Integration\Incoming\Http\Controllers;

class HomeController
{
    public function index(): void
    {
        echo 'index Page';
    }

    public function one(int $id): void
    {
        echo 'one Page';
    }

    public function two(int $firstId, int $secId): void
    {
        echo 'f: ' . $firstId . ' ' . 'sec: ' . $secId;
    }
}