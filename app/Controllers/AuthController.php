<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\EmailAuth;
use App\Auth\JWT;
use App\Auth\JWTAuthStrategy;
use App\Auth\TokenAuth;
use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use Exception;

class AuthController implements ControllerInterface
{
    private string $entityMethod;
    private DBConnection $primaryDB;

    private DBConnection $replicaDB;

    private int $uriEmbeddedParam;
    private array $bodyParams;

    private CacheInterface $cache;
    private JWT $jwt;
    private Auth $auth;

    /**
     * @throws Exception
     */
    public function __construct(
        array          $uriParams,
        array          $bodyParams,
        string         $entityMethod,
        int            $uriEmbeddedParam,
        DBConnection   $primaryDB,
        DBConnection   $replicaDB,
        CacheInterface $cache,
    ) {
        $this->entityMethod = $entityMethod;
        $this->uriEmbeddedParam = $uriEmbeddedParam;
        $this->bodyParams = $bodyParams;
        $this->primaryDB = $primaryDB;
        $this->replicaDB = $replicaDB;
        $this->cache = $cache;

        $this->jwt = new JWT('your_secret_here', 'HS256', 3600);
        $this->auth = new Auth($cache);
    }



    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function login(): void
    {
        $request = $this->bodyParams;

        if (isset($request['email'])) {
            $this->auth->setStrategy(new JWTAuthStrategy($this->primaryDB, $this->cache, $this->jwt));
        } elseif (isset($request['token'])) {
            $this->auth->setStrategy(new TokenAuth($this->primaryDB, $this->cache));
        }

        try {
            $token = $this->auth->login($request);
            echo json_encode([
                'success' => true,
                'user' => $this->auth->user(),
                'token' => $token
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function register(): void
    {
        $request = $this->bodyParams;

        if (empty($request['email']) || empty($request['password'])) {
            echo json_encode(['success' => false, 'error' => 'Email and password are required']);
            return;
        }

        $this->auth->setStrategy(new EmailAuth($this->primaryDB, $this->cache));

        try {
            $result = $this->auth->register($request);
            echo json_encode([
                'success' => true,
                'user' => $this->auth->user(),
                'result' => $result
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

}