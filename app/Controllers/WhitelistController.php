<?php

namespace App\Controllers;

 use App\DB\Contracts\DBConnection;
 use App\Repositories\WhitelistRepository;
 use Exception;

 readonly class WhitelistController implements ControllerInterface
 {
     private string $entityMethod;
     private DBConnection $db;
     private int $uriEmbeddedParam;
     private array $bodyParams;

     private WhitelistRepository $repo;

     /**
      * @throws Exception
      */
     public function __construct(
         array                      $uriParams,
         array                      $bodyParams,
         string                     $entityMethod,
         int                        $uriEmbeddedParam,
         DBConnection $db,
     ) {
            $this->entityMethod = $entityMethod;
            $this->uriEmbeddedParam = $uriEmbeddedParam;
            $this->bodyParams = $bodyParams;
            $this->db = $db;
            $this->repo = new  WhitelistRepository($this->db);
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
         return $this->repo->all();
     }

     /**
      * @throws Exception
      */
     public function store(): string
     {
         $this->repo->store($this->bodyParams);
         return 'stored!';
     }

     /**
      * @throws Exception
      */
     public function update(): string
     {
         $this->repo->update($this->uriEmbeddedParam, $this->bodyParams);
         return 'updated!';
     }

     /**
      * @throws Exception
      */
     public function delete(): string
     {
         $this->repo->delete($this->uriEmbeddedParam);
         return 'deleted';
     }
 }
