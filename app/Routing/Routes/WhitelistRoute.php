<?php

namespace App\Routing\Routes;

use App\Controllers\WhitelistController;
use App\Routing\Contracts\ARoute;

class WhitelistRoute extends ARoute
{
    public function getRoutes(): array
    {
        $this->add('GET', '/api/whitelist',  WhitelistController::class, 'index');
        $this->add('POST', '/api/whitelist', WhitelistController::class, 'store');
        $this->add('PUT', '/api/whitelist',  WhitelistController::class, 'update', ['int']);
        $this->add('DELETE', '/api/whitelist', WhitelistController::class, 'delete', ['int']);

        return $this->routesContainer;
    }
}