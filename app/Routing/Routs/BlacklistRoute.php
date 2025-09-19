<?php

namespace App\Routing\Routs;

use App\Controllers\BlacklistController;

class BlacklistRoute extends AEndpointSuperClass
{

    public function endpoints(): array
    {
        $this->add('GET', '/blacklist', BlacklistController::class, 'index', []);
        $this->add('POST', '/blacklist', BlacklistController::class, 'create', []);
        $this->add('PUT', '/blacklist', BlacklistController::class, 'update', ['int']);
        $this->add('DELETE', '/blacklist', BlacklistController::class, 'delete', ['int']);

        return $this->endpointsContainer;
    }

}