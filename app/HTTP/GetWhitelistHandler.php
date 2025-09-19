<?php

namespace App\HTTP;

use App\Services\WhitelistService;

class GetWhitelistHandler implements HttpMethodHandler {
    public function __construct(private WhitelistService $service) {}
    public function handle(array $uriParams, array $bodyParams): mixed {
        return $this->service->getAll();
    }
}
