<?php

namespace App\Middlewares;

use App\Http\RequestDTO;

interface MiddlewareInterface
{
    public function handle(RequestDTO $request, callable $next);
}
