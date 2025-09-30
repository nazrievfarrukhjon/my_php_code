<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\RideRepository;
use Exception;

readonly class RideController implements ControllerInterface
{
    public function __construct(private RideRepository $repository) {}

    public function requestRide(RequestDTO $requestDTO): array
    {
        return $this->repository->createRide($requestDTO->bodyParams);
    }

    /**
     * @throws Exception
     */
    public function acceptRide(RequestDTO $requestDTO): array
    {
        return $this->repository->acceptRide(
            $requestDTO->bodyParams['ride_id'],
            $requestDTO->bodyParams['driver_id'],
        );
    }

    /**
     * @throws Exception
     */
    public function startRide(RequestDTO $requestDTO): array
    {
        return $this->repository->startRide($requestDTO->bodyParams['ride_id']);
    }

    /**
     * @throws Exception
     */
    public function completeRide(RequestDTO $requestDTO): array
    {
        return $this->repository->completeRide($requestDTO->bodyParams['ride_id']);
    }

    /**
     * @throws Exception
     */
    public function getStatus(RequestDTO $requestDTO): array
    {
        return $this->repository->getRideStatus($requestDTO->bodyParams['ride_id']);
    }
}
