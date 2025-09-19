<?php

namespace App\Controllers;

 use App\DB\MyDB;
 use App\Entity\Whitelist;
 use Exception;

 readonly class WhitelistController implements ControllerInterface
 {
     private string $entityMethod;
     private MyDB $myDb;
     private int $uriEmbeddedParam;
     private array $bodyParams;

     public function __construct(
         array $uriParams,
          array $bodyParams,
          string $entityMethod,
          int $uriEmbeddedParam,
          MyDB $myDb
     ) {
            $this->entityMethod = $entityMethod;
            $this->myDb = $myDb;
            $this->uriEmbeddedParam = $uriEmbeddedParam;
            $this->bodyParams = $bodyParams;
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
         $whitelist = new Whitelist($this->myDb);
         return $whitelist->all();
     }

     /**
      * @throws Exception
      */
     public function store(): string
     {
         $whitelist = new Whitelist($this->myDb);
         $whitelist->store($this->bodyParams);
         return 'stored!';
     }

     /**
      * @throws Exception
      */
     public function update(): string
     {
         $whitelist = new Whitelist($this->myDb);
         $whitelist->update($this->uriEmbeddedParam, $this->bodyParams);
         return 'updated!';
     }

     /**
      * @throws Exception
      */
     public function delete(): string
     {
         $whitelist = new Whitelist($this->myDb);
         $whitelist->delete($this->uriEmbeddedParam);
         return 'deleted';
     }
 }
