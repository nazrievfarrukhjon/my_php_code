<?php

namespace App\Controllers;

 use App\DB\DBConnection;
 use App\Repositories\WhitelistRepository;
 use Exception;

 readonly class WhitelistController implements ControllerInterface
 {
     private string $entityMethod;
     private DBConnection $db;
     private int $uriEmbeddedParam;
     private array $bodyParams;

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
         $whitelist = new WhitelistRepository($this->db);
         return $whitelist->all();
     }

     /**
      * @throws Exception
      */
     public function store(): string
     {
         $whitelist = new WhitelistRepository($this->db);
         $whitelist->store($this->bodyParams);
         return 'stored!';
     }

     /**
      * @throws Exception
      */
     public function update(): string
     {
         $whitelist = new WhitelistRepository($this->db);
         $whitelist->update($this->uriEmbeddedParam, $this->bodyParams);
         return 'updated!';
     }

     /**
      * @throws Exception
      */
     public function delete(): string
     {
         $whitelist = new WhitelistRepository($this->db);
         $whitelist->delete($this->uriEmbeddedParam);
         return 'deleted';
     }
 }
