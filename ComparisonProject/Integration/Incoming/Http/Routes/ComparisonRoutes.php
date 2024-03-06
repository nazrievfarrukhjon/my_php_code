<?php

namespace Comparison\Integration\Incoming\Http\Routes;

use Integration\Incoming\Http\Routes\Router;
use Integration\Incoming\Http\Routes\WhitelistedController;
use JetBrains\PhpStorm\NoReturn;

class ComparisonRoutes extends Router
{
    #[NoReturn] public function __invoke(): void
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');
    }

}
