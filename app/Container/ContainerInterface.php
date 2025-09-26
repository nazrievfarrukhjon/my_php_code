<?php

namespace App\Container;

use App\Exceptions\ContainerExceptionInterface;
use App\Exceptions\NotFoundExceptionInterface;


interface ContainerInterface
{

    public function get($id);


    public function has($id);
}