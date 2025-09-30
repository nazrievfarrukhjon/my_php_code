<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\DriverLocationRepository;

readonly class DriverLocationController implements ControllerInterface
{
    public function __construct(private DriverLocationRepository $repository){}

    public function storeDriverLocation(RequestDTO $requestDTO): array
    {
        return $this->repository->storeDriverLocation($requestDTO->bodyParams);
    }
}