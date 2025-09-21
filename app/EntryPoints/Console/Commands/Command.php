<?php

namespace App\EntryPoints\Console\Commands;

interface Command {
    public function execute(): void;
}