<?php

namespace App\HTTP;

class AuthMiddleware implements Middleware
{
    public function process(HttpRequest $request, callable $next): mixed
    {
        if (!$request->isAuthenticated()) {
            throw new UnauthorizedException();
        }
        return $next($request);
    }
}