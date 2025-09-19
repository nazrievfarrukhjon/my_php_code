<?php

namespace App\Routing\Routs;

class WhitelistRoute extends AEndpointSuperClass
{
    public function endpoints(): array
    {
        $this->add('GET', '/whitelist', 'whitelistProxy', 'index');
        $this->add('POST', '/whitelist', 'whitelistProxy', 'store');
        $this->add('PUT', '/whitelist', 'whitelistProxy', 'update', ['int']);
        $this->add('DELETE', '/whitelist', 'whitelistProxy', 'delete', ['int']);

        return $this->endpointsContainer;
    }
}