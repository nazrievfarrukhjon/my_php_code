<?php

namespace App\Routing\Routs;

use App\Proxy\BlacklistProxy;

class BlacklistRoute extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/a', BlacklistProxy::class, 'index', []);
        $this->add('POST', '/a', BlacklistProxy::class, 'store', []);
        $this->add('PUT', '/a', BlacklistProxy::class, 'update', ['int']);
        $this->add('DELETE', '/a', BlacklistProxy::class, 'update', ['int']);

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