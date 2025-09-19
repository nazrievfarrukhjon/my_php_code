<?php

namespace App\Proxy;

 use App\DB\MyDB;
 use App\Entity\Blacklist;
 use Exception;

 readonly class BlacklistProxy implements IProxy
{
    public function __construct(
        private array  $uriParams,
        private array  $bodyParams,
        private string $entityMethod,
        private int $uriEmbeddedParam,
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
        $blacklist = new Blacklist(new MyDB());

        return $blacklist->all();
    }

     /**
      * @throws Exception
      */
     public function store(): string
     {
        (new Blacklist(new MyDB()))->store($this->bodyParams);

        return 'stored!';
    }

     /**
      * @throws Exception
      */
     public function update(): string
    {
        (new Blacklist(new MyDB()))->update($this->uriEmbeddedParam, $this->bodyParams);

        return 'updated!';
    }

     /**
      * @throws Exception
      */
     public function delete(): string
    {
        (new Blacklist(new MyDB()))->delete($this->uriEmbeddedParam);

        return 'deleted';
    }
}