<?php

namespace App\Middlewares;

use App\Auth\Auth;
use App\Auth\TokenAuth;
use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;

readonly class AuthMiddleware implements MiddlewareInterface {

    public function __construct(
        private DBConnection   $db,
        private CacheInterface $cache
    ){}

    /**
     * @throws \Exception
     */
    public function handle(array $request, callable $next) {
        $auth = Auth::getInstance($this->cache);

        // Check for Authorization header
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        $auth->setStrategy(new TokenAuth($this->db, $this->cache));

        if (!$auth->check() && $header) {
            $auth->authenticateBearer($header);
        }

        if (!$auth->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        return $next($request);
    }
}
