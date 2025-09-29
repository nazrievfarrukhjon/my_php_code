<?php

namespace App\Controllers;

use App\DB\Contracts\DBConnection;
use App\Http\RequestDTO;
use Exception;

class WelcomeController implements ControllerInterface
{
    private DBConnection $db;

    /**
     * @throws Exception
     */
    public function __construct(
        DBConnection $db
    ) {
        $this->db = $db;
    }

    /**
     * @throws Exception
     */
    public function index(RequestDTO $requestDTO): array
    {
        return ['this is welcome page'];
    }

    public function favicon(RequestDTO $requestDTO): void
    {
        header('Content-Type: image/x-icon');
        readfile(ROOT_DIR . '/public/favicon.png');
        exit;
    }

}