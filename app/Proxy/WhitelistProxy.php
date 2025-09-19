<?php

namespace App\Proxy;

 use App\DB\MyDB;
 use App\Entity\Whitelist;
 use Exception;

 readonly class WhitelistProxy implements IProxy
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
        $whitelist = new Whitelist(new MyDB());

        return $whitelist->all();
    }

     /**
      * @throws Exception
      */
     public function store(): string
     {
        (new Whitelist(new MyDB()))->store($this->bodyParams);

        return 'stored!';
    }

     /**
      * @throws Exception
      */
     public function update(): string
    {
        (new Whitelist(new MyDB()))->update($this->uriEmbeddedParam, $this->bodyParams);

        return 'updated!';
    }

     /**
      * @throws Exception
      */
     public function delete(): string
    {
        (new Whitelist(new MyDB()))->delete($this->uriEmbeddedParam);

        return 'deleted';
    }
}