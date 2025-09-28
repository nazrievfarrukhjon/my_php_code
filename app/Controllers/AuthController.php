<?php

namespace App\Controllers;

use App\Auth\Auth;
use App\Auth\EmailAuth;
use App\Auth\TokenAuth;
use App\DB\Contracts\DBConnection;
use Exception;

class AuthController implements ControllerInterface
{
    private string $entityMethod;
    private DBConnection $db;

    private DBConnection $replicaDB;

    private int $uriEmbeddedParam;
    private array $bodyParams;

    /**
     * @throws Exception
     */
    public function __construct(
        array                      $uriParams,
        array                      $bodyParams,
        string                     $entityMethod,
        int                        $uriEmbeddedParam,
        DBConnection $db,
        DBConnection $replicatDB,
    ) {
        $this->entityMethod = $entityMethod;
        $this->uriEmbeddedParam = $uriEmbeddedParam;
        $this->bodyParams = $bodyParams;
        $this->db = $db;
        $this->replicaDB = $replicatDB;
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    public function login(): void
    {
        $request = $this->bodyParams;
        $auth = Auth::getInstance();

        if (isset($request['email'])) {
            $auth->setStrategy(new EmailAuth($this->db));
        } elseif (isset($request['token'])) {
            $auth->setStrategy(new TokenAuth($this->db));
        }

        try {
            $auth->login($request);
            echo json_encode(['success' => true, 'user' => $auth->user()]);
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
            $auth->setStrategy(new EmailAuth($this->db));
            $result = $auth->register($request);
            echo json_encode(['success' => true, 'user' => $auth->user(), 'result' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }

    }

}