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
        $connection = (new MyDB())->connection();

        $blacklist = new Blacklist($connection);

        return $blacklist->all();
    }

    public function store(): string
    {
        return 'store';

    }

    public function update(): string
    {
        return 'update';

    }

    public function delete(): string
    {
        return 'delete';

    }
}