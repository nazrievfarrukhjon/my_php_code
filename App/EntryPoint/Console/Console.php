<?php

namespace App\EntryPoint\Console;
class Console
{
    public function __construct(
        private string $commandName,
        private string $argOne,
        private string $argTwo
    )
    {
    }


    public function handleCliCommand(): void
    {
        if ($this->commandName === 'migrate' && $this->argOne === 'absent') {

        }

        $directory = __DIR__ . '/../../Migrations';
        $fileNames = scandir($directory);
        $fileNames = array_diff($fileNames, ['.', '..']);

        foreach ($fileNames as $file) {
            if ($this->startsWithNumeric($file)) {

                require_once $directory . '/' . $file;
                $className = basename($file, '.php');
                $exploded = explode('_', $className);
                $className = 'App\Migrations' . '\\' . $exploded[1];
                $migration = new $className;
                $migration->migrate();
            }
        }

    }

    public function response()
    {
        return;
    }

    private function startsWithNumeric($str): bool
    {
        return preg_match('/^\d/', $str) === 1;
    }

}