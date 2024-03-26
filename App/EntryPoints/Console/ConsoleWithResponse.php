<?php

namespace App\EntryPoints\Console;

use App\EntryPoints\Console\Console;

readonly class ConsoleWithResponse
{

    public function __construct(private Console $console)
    {
    }

    public function response(): string
    {
        $this->console->handleCliCommand();

        $result = $this->console->response();

        return json_encode($result);
    }
}
