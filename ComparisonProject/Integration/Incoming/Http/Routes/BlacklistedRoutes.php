<?php

namespace Comparison;

use Integration\Incoming\Http\Routes\Router;
use JetBrains\PhpStorm\NoReturn;

class BlacklistedRoutes extends Router
{
    #[NoReturn] public function __invoke(): void
    {
        $this->setPrefix('blacklisted');
        $this->post('/', BlacklistedController::class, 'save');
    }

}
