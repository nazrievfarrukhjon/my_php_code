<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Repositories\DriverRepository;

readonly class DriverController implements ControllerInterface
{
    public function __construct(private DriverRepository $repository){}

    public function storeDriverLocation(RequestDTO $requestDTO): array
    {
        $body = $requestDTO->bodyParams;
        if (empty($body['driver_id']) || !isset($body['latitude']) || !isset($body['longitude'])) {
            return [
                'success' => false,
                'error' => 'driver_id, latitude, and longitude are required'
            ];
        }

        $location = $this->repository->storeDriverLocation($body);

        return [
            'success' => true,
            'data' => $location
        ];
    }

    public function createDriver(RequestDTO $requestDTO): array
    {
        return $this->repository->storeDriver($requestDTO->bodyParams);
    }
}