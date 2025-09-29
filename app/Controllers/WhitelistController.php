<?php

namespace App\Controllers;

 use App\DB\Contracts\DBConnection;
 use App\Http\RequestDTO;
 use App\Repositories\WhitelistRepository;
 use Exception;

 readonly class WhitelistController implements ControllerInterface
 {
     private DBConnection $db;

     private WhitelistRepository $repo;

     /**
      * @throws Exception
      */
     public function __construct(
         DBConnection $db,
     ) {
            $this->db = $db;
            $this->repo = new  WhitelistRepository($this->db);
     }

     /**
      * @throws Exception
      */
     public function index(RequestDTO $requestDTO): array
     {
         return $this->repo->all();
     }

     /**
      * @throws Exception
      */
     public function store(RequestDTO $requestDTO): string
     {
         $this->repo->store($requestDTO->bodyParams);
         return 'stored!';
     }

     /**
      * @throws Exception
      */
     public function update(RequestDTO $requestDTO): string
     {
         $this->repo->update($requestDTO->uriEmbeddedParam, $requestDTO->bodyParams);
         return 'updated!';
     }

     /**
      * @throws Exception
      */
     public function delete(RequestDTO $requestDTO): string
     {
         $this->repo->delete($requestDTO->uriEmbeddedParam);
         return 'deleted';
     }
 }
