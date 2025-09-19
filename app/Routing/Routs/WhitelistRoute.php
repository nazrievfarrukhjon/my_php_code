<?php

namespace App\Routing\Routs;

use App\Proxy\WhitelistProxy;

class WhitelistRoute extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/whitelist', WhitelistProxy::class, 'index', []);
        $this->add('POST', '/whitelist', WhitelistProxy::class, 'store', []);
        $this->add('PUT', '/whitelist', WhitelistProxy::class, 'update', ['int']);
        $this->add('DELETE', '/whitelist', WhitelistProxy::class, 'delete', ['int']);

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