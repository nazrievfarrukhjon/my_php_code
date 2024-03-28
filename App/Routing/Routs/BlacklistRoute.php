<?php

namespace App\Routing\Routs;

use App\Proxy\BlacklistProxy;

class BlacklistRoute extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/blacklist', BlacklistProxy::class, 'index', []);
        $this->add('POST', '/blacklist', BlacklistProxy::class, 'store', []);
        $this->add('PUT', '/blacklist', BlacklistProxy::class, 'update', ['int']);
        $this->add('DELETE', '/blacklist', BlacklistProxy::class, 'update', ['int']);

        return $this->endpointsContainer;
    }

    private function add(
        string $httpMethod,
        string $uri,
        string $entity,
        string $entityMethod,
        array  $argsRules
    ): void {
        $this->endpointsContainer[$httpMethod][$uri] = [$entity, $entityMethod, $argsRules];
    }

}