<?php

namespace App\EntryPoint\Console;

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