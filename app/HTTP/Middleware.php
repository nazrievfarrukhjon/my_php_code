<?php

namespace App\HTTP;

use App\EntryPoints\Http\WebRequest;

interface Middleware {
    public function process(WebRequest $request, callable $next): mixed;
}

