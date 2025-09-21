<?php

namespace App\Controllers;

use App\DB\Contracts\DBConnection;
use Exception;

readonly class WelcomeController implements ControllerInterface
{
    private string $entityMethod;

    public function __construct(
        string $entityMethod,
    ) {
        $this->entityMethod = $entityMethod;
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