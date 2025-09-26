<?php

namespace App\Middlewares;

interface MiddlewareInterface
{
    public function handle(array $request, callable $next);
}
