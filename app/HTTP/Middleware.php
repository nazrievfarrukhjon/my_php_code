<?php

namespace App\HTTP;


use App\EntryPoints\Http\HttpRequest;

interface Middleware {
    public function process(HttpRequest $request, callable $next): mixed;
}

