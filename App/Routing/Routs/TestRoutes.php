<?php

namespace App\Routing\Routs;

use App\Proxy\ProxyA;

class TestRoutes extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/a', ProxyA::class, 'index', []);
        $this->add('POST', '/a', ProxyA::class, 'store', []);
        $this->add('PUT', '/a', ProxyA::class, 'update', ['int']);
        $this->add('DELETE', '/a', ProxyA::class, 'update', ['int']);

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