<?php

namespace App\Routing\Routs;

use App\Controllers\WhitelistController;

class WhitelistRoute extends AEndpointSuperClass
{
    public function endpoints(): array
    {
        $this->add('GET', '/whitelist',  WhitelistController::class, 'index');
        $this->add('POST', '/whitelist', WhitelistController::class, 'store');
        $this->add('PUT', '/whitelist',  WhitelistController::class, 'update', ['int']);
        $this->add('DELETE', '/whitelist', WhitelistController::class, 'delete', ['int']);

        return $this->endpointsContainer;
    }
}