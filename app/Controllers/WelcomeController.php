<?php

namespace App\Controllers;

use App\DB\Contracts\DBConnection;
use App\Repositories\BlacklistRepository;
use Exception;

class WelcomeController implements ControllerInterface
{
    private string $entityMethod;
    private DBConnection $db;
    private int $uriEmbeddedParam;
    private array $bodyParams;

    private BlacklistRepository $repository;

    /**
     * @throws Exception
     */
    public function __construct(
        array $uriParams,
        array $bodyParams,
        string $entityMethod,
        int $uriEmbeddedParam,
        DBConnection $db
    ) {
        $this->entityMethod = $entityMethod;
        $this->db = $db;
        $this->uriEmbeddedParam = $uriEmbeddedParam;
        $this->bodyParams = $bodyParams;
        $this->repository = new BlacklistRepository($this->db);
    }

    public function __invoke()
    {
        return call_user_func([$this, $this->entityMethod]);

    }

    /**
     * @throws Exception
     */
    public function index(): array
    {
        return ['this is welcome page'];
    }

    public function favicon(): void
    {
        header('Content-Type: image/x-icon');
        readfile(ROOT_DIR . '/public/favicon.png');
        exit;
    }

}