<?php

namespace App\Controllers;

 use App\DB\DBConnection;
 use App\Repositories\BlacklistRepository;
 use Exception;

 readonly class BlacklistController implements ControllerInterface
{
     private string $entityMethod;
     private DBConnection $db;
     private int $uriEmbeddedParam;
     private array $bodyParams;

     public function __construct(
         array $uriParams,
         array $bodyParams,
         string $entityMethod,
         int $uriEmbeddedParam,
         Database $db
     ) {
         $this->entityMethod = $entityMethod;
         $this->db = $db;
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
        $blacklist = new BlacklistRepository($this->db);

        return $blacklist->all();
    }

     /**
      * @throws Exception
      */
     public function store(): string
     {
        (new BlacklistRepository($this->db))->store($this->bodyParams);

        return 'stored!';
    }

     /**
      * @throws Exception
      */
     public function update(): string
    {
        (new BlacklistRepository($this->db))->update($this->uriEmbeddedParam, $this->bodyParams);

        return 'updated!';
    }

     /**
      * @throws Exception
      */
     public function delete(): string
    {
        (new BlacklistRepository($this->db))->delete($this->uriEmbeddedParam);

        return 'deleted';
    }
}