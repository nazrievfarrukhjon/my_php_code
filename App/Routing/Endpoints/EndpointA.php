<?php

namespace App\Routing\Endpoints;

use App\Entity\EntityA;

class EndpointA extends EndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/a', EntityA::class, 'index', []);
        $this->add('POST', '/a', EntityA::class, 'store', []);
        $this->add('PUT', '/a', EntityA::class, 'update', ['int']);
        $this->add('DELETE', '/a', EntityA::class, 'update', ['int']);

        return $this->endpointsContainer;
    }

    private function add(
        string $httpMethod,
        string $uri,
        string $entity,
        string $entityMethod,
        array  $argsRules
    ): void
    {
        $this->endpointsContainer[$httpMethod][$uri] = [$entity, $entityMethod, $argsRules];
    }

}