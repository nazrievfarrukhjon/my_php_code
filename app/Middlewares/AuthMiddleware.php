<?php

namespace App\Middlewares;

use App\Auth\Auth;
use App\Auth\JWT;
use App\Auth\JWTAuthStrategy;
use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use App\Http\RequestDTO;
use Exception;

readonly class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private DBConnection $db,
        private CacheInterface $cache
    ) {}

    /**
     * @throws Exception
     */
    public function handle(RequestDTO $request, callable $next)
    {
        $auth = Auth::getInstance($this->cache);

        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!$auth->check()) {
            $auth->setStrategy(
                new JWTAuthStrategy(
                    $this->db, $this->cache,
                    new JWT('your_secret_here', 'HS256', 3600)
                )
            );

            if ($header && str_starts_with($header, 'Bearer ')) {
                $auth->authenticateBearer($header);
            }
        }

        if (!$auth->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        return $next($request);
    }
}
