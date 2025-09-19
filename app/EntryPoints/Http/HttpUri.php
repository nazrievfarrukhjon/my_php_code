<?php

namespace App\EntryPoints\Http;

readonly class HttpUri
{

    public function __construct(
        private string $httpUri,
        private string $httpMethod
    ) {}

    public function uriEmbeddedParam(): int
    {
        $allowedMethods = ['DELETE', 'PUT', 'PATCH'];

        if (in_array($_SERVER['REQUEST_METHOD'], $allowedMethods)) {
            $uris = explode('/', $this->httpUri);
            return end($uris);
        }
        return -1;
    }

    public function cleanUri(): string
    {
        $uri = $this->httpUri;
        if (in_array($this->httpMethod, ['DELETE', 'PUT', 'PATCH'])) {
            $lastSlashPos = strrpos($uri, '/');
            if ($lastSlashPos !== false) {
                return substr($uri, 0, $lastSlashPos);
            }
        }
        return $uri;
    }

}