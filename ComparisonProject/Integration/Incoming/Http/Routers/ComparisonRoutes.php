<?php

namespace Comparison\Integration\Incoming\Http\Routers;

use Integration\Incoming\Http\Routers\Router;
use Integration\Incoming\Http\Routers\WhitelistedController;
use JetBrains\PhpStorm\NoReturn;

class ComparisonRoutes extends Router
{
    #[NoReturn] public function __invoke(): void
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');
    }

}
