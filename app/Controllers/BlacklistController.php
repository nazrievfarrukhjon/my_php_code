<?php

namespace App\Controllers;

 use App\DB\MyDB;
 use App\Entity\Blacklist;
 use Exception;

 readonly class BlacklistController implements ControllerInterface
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
        $blacklist = new Blacklist($this->myDb);

        return $blacklist->all();
    }

     /**
      * @throws Exception
      */
     public function store(): string
     {
        (new Blacklist($this->myDb))->store($this->bodyParams);

        return 'stored!';
    }

     /**
      * @throws Exception
      */
     public function update(): string
    {
        (new Blacklist($this->myDb))->update($this->uriEmbeddedParam, $this->bodyParams);

        return 'updated!';
    }

     /**
      * @throws Exception
      */
     public function delete(): string
    {
        (new Blacklist($this->myDb))->delete($this->uriEmbeddedParam);

        return 'deleted';
    }
}