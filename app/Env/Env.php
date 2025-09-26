<?php

namespace App\Env;

class Env
{
    private array $vars;

    public function __construct(string $filePath)
    {
        $this->vars = [];
        if ($file = fopen($filePath, 'r')) {
            while (($line = fgets($file)) !== false) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) continue;

                [$key, $value] = explode('=', $line, 2);
                $this->vars[$key] = trim($value, '"');
            }
            fclose($file);
        }
    }

    public function get(string $key, $default = null): mixed
    {
        return $this->vars[$key] ?? $default;
    }
}
