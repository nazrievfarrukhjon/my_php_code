<?php

namespace App\Integration\Incoming\Http\Routes;

use App\Integration\Incoming\Http\Routes\Router;
//use App\Comparison\Integration\Incoming\Http\Controllers\WhitelistedController;
use Comparison\Integration\Incoming\Http\Controllers\WhitelistedController;

class WhitelistedRoutes extends Router
{
    public function __invoke(): void
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');
    }

}
