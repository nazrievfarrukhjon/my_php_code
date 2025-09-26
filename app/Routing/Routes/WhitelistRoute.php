<?php

namespace App\Routing\Routes;

use App\Controllers\WhitelistController;
use App\Routing\Contracts\ARoute;

class WhitelistRoute extends ARoute
{
    public function getRoutes(): array
    {
        $this->add('GET', '/whitelist',  WhitelistController::class, 'index');
        $this->add('POST', '/whitelist', WhitelistController::class, 'store');
        $this->add('PUT', '/whitelist',  WhitelistController::class, 'update', ['int']);
        $this->add('DELETE', '/whitelist', WhitelistController::class, 'delete', ['int']);

        return $this->routesContainer;
    }
}