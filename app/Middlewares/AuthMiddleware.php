<?php

namespace App\Middlewares;

use App\Auth\Auth;

class AuthMiddleware implements MiddlewareInterface {

    public function __construct()
    {
    }

    public function handle(array $request, callable $next) {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        return $next($request);
    }
}
