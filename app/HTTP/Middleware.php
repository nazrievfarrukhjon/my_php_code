<?php

namespace App\HTTP;


use App\EntryPoints\Http\MyHttpRequest;

interface Middleware {
    public function process(MyHttpRequest $request, callable $next): mixed;
}

