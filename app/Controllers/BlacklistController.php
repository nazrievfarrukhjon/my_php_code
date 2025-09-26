<?php

namespace App\Controllers;

 use App\DB\Contracts\DBConnection;
 use App\Repositories\BlacklistRepository;
 use Exception;

 readonly class BlacklistController implements ControllerInterface
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
        return $this->repository->all();
    }

     /**
      * @throws Exception
      */
     public function store(): string
     {
         $this->repository->store($this->bodyParams);

        return 'stored!';
    }

     /**
      * @throws Exception
      */
     public function update(): string
    {
        $this->repository->update($this->uriEmbeddedParam, $this->bodyParams);

        return 'updated!';
    }

     /**
      * @throws Exception
      */
     public function delete(): string
    {
        $this->repository->delete($this->uriEmbeddedParam);

        return 'deleted';
    }
}