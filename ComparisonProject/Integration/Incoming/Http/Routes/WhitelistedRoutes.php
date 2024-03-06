<?php

namespace App\Integration\Incoming\Http\Routes;

use App\Integration\Incoming\Http\Routes\RouterBuilder;
//use App\Comparison\Integration\Incoming\Http\Controllers\WhitelistedController;
use Comparison\Integration\Incoming\Http\Controllers\WhitelistedController;

class WhitelistedRoutes extends RouterBuilder
{
    public function __invoke(): void
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');
    }

}
