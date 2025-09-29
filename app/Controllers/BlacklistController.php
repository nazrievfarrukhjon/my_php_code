<?php

namespace App\Controllers;

use App\DB\Contracts\DBConnection;
use App\Http\RequestDTO;
use App\Repositories\BlacklistRepository;
use Exception;

readonly class BlacklistController implements ControllerInterface
{
    private DBConnection $primaryDB;
    private DBConnection $replicaDB;

    private BlacklistRepository $repository;

    /**
     * @throws Exception
     */
    public function __construct(
        DBConnection $primaryDB,
        DBConnection $replicaDB,
    )
    {
        $this->primaryDB = $primaryDB;
        $this->replicaDB = $replicaDB;
        $this->repository = new BlacklistRepository($this->primaryDB, $this->replicaDB);
    }

    /**
     * @throws Exception
     */
    public function index(RequestDTO $requestDTO): array
    {
        return $this->repository->all();
    }

    /**
     * @throws Exception
     */
    public function store(RequestDTO $requestDTO): string
    {
        $this->repository->store($requestDTO->bodyParams);

        return 'stored!';
    }

    /**
     * @throws Exception
     */
    public function update(RequestDTO $requestDTO): string
    {
        $this->repository->update($requestDTO->uriEmbeddedParam, $requestDTO->bodyParams);

        return 'updated!';
    }

    /**
     * @throws Exception
     */
    public function delete(RequestDTO $requestDTO): string
    {
        $this->repository->delete($requestDTO->uriEmbeddedParam);

        return 'deleted';
    }

    public function searchByName(RequestDTO $requestDTO): array
    {
        $name = $requestDTO->bodyParams['name'];
        $birthDate = $requestDTO->bodyParams['birthdate']?? null;

        return $this->repository->searchByName($name, $birthDate);
    }
}