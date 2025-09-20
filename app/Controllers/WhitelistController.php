<?php

namespace App\Controllers;

 use App\DB\DatabaseConnectionInterface;
 use App\Entity\Whitelist;
 use Exception;

 readonly class WhitelistController implements ControllerInterface
 {
     private string $entityMethod;
     private DatabaseConnectionInterface $db;
     private int $uriEmbeddedParam;
     private array $bodyParams;

     public function __construct(
         array                      $uriParams,
         array                      $bodyParams,
         string                     $entityMethod,
         int                        $uriEmbeddedParam,
         DatabaseConnectionInterface $db,
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
         $whitelist = new Whitelist($this->db);
         return $whitelist->all();
     }

     /**
      * @throws Exception
      */
     public function store(): string
     {
         $whitelist = new Whitelist($this->db);
         $whitelist->store($this->bodyParams);
         return 'stored!';
     }

     /**
      * @throws Exception
      */
     public function update(): string
     {
         $whitelist = new Whitelist($this->db);
         $whitelist->update($this->uriEmbeddedParam, $this->bodyParams);
         return 'updated!';
     }

     /**
      * @throws Exception
      */
     public function delete(): string
     {
         $whitelist = new Whitelist($this->db);
         $whitelist->delete($this->uriEmbeddedParam);
         return 'deleted';
     }
 }
