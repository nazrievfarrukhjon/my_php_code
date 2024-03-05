<?php

namespace src\Terrorists\Routes;

use Integration\Incoming\Http\Routes\Router;
use JetBrains\PhpStorm\NoReturn;

class WhitelistedRoutes extends Router
{
    #[NoReturn] public function __invoke(): void
    {
        $this->setPrefix('whitelisted');
        $this->post('/', WhitelistedController::class, 'save');
    }

}
