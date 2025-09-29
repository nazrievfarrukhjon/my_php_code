<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\DriverRepository;

readonly class DriverController implements ControllerInterface
{
    public function __construct(private DriverRepository $repository){}

    public function storeDriverLocation(RequestDTO $requestDTO): array
    {
        return $this->repository->storeDriverLocation($requestDTO->bodyParams);
    }
}