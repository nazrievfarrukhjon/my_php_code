<?php

namespace App\Controllers;

use App\DB\DBConnection;
use Exception;

readonly class WelcomeController implements ControllerInterface
{
    private string $entityMethod;
    private DBConnection $db;
    private int $uriEmbeddedParam;
    private array $bodyParams;

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


}