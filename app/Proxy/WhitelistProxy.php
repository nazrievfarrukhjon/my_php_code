<?php

namespace App\Proxy;

 use App\DB\MyDB;
 use App\Entity\Whitelist;
 use Exception;

 readonly class WhitelistProxy implements IProxy
 {
     public function __construct(
         private array $uriParams,
         private array $bodyParams,
         private string $entityMethod,
         private int $uriEmbeddedParam,
         private MyDB $myDb
     ) {}

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
