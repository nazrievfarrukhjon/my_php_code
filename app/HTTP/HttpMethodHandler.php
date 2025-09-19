<?php

namespace App\HTTP;

interface HttpMethodHandler {
    public function handle(array $uriParams, array $bodyParams): mixed;
}


