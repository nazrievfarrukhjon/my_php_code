<?php

namespace App\Routing\Routs;

use App\Proxy\WelcomeProxy;

class WelcomeRoutes extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/', WelcomeProxy::class, 'index', []);

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