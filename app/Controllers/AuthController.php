<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\EmailAuth;
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
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function login(): void
    {
        $request = $this->bodyParams;
        $auth = Auth::getInstance($this->cache);

        if (isset($request['email'])) {
            $auth->setStrategy(new EmailAuth($this->primaryDB, $this->cache));
        } elseif (isset($request['token'])) {
            $auth->setStrategy(new TokenAuth($this->primaryDB, $this->cache));
        }

        try {
            $token = $auth->login($request);
            echo json_encode(['success' => true, 'user' => $auth->user(), 'token' => $token]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function register(): void
    {
        try {
            $request = $this->bodyParams;
            //
            if (empty($request['email']) || empty($request['password'])) {
                throw new Exception("Email and password are required");
            }

            //
            $auth = Auth::getInstance();
            $auth->setStrategy(new EmailAuth($this->primaryDB));
            $result = $auth->register($request);
            echo json_encode(['success' => true, 'user' => $auth->user(), 'result' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

    }

}