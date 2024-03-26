<?php

namespace App\Migrations\Operations;
class MigrationFilesSqlQuery
{
    public function query(string $method): void
    {
        $directory = __DIR__ . '/../../Migrations';
        $fileNames = scandir($directory);

        $fileNames = array_filter($fileNames, function ($fileName) {
            return preg_match('/^\d+_/',$fileName) === 1;
        });

        foreach ($fileNames as $file) {
            if ($this->startsWithNumeric($file)) {

                require_once $directory . '/' . $file;
                $className = basename($file, '.php');
                $exploded = explode('_', $className);
                $className = 'App\Migrations' . '\\' . $exploded[1];
                $migration = new $className;
                $migration->$method();
            }
        }
    }

    private function startsWithNumeric($str): bool
    {
        return preg_match('/^\d/', $str) === 1;
    }
}