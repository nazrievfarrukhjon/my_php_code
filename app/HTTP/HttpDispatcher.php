<?php

namespace App\HTTP;

use Exception;

class HttpDispatcher {
    private array $strategyMap;

    public function __construct(array $handlers) {
        $this->strategyMap = $handlers;
    }

    /**
     * @throws Exception
     */
    public function dispatch(string $httpMethod, array $uriParams, array $bodyParams): mixed {
        $handler = $this->strategyMap[$httpMethod]
            ?? throw new Exception("Unsupported method");

        return $handler->handle($uriParams, $bodyParams);
    }
}
