<?php

namespace App\EntryPoints\Http;

use App\Routing\RoutesRegistration;
use App\Routing\UrlAssociatedToProxy;
use Exception;

readonly class HttpRequest
{
    public function __construct(
        private string $httpUri,
        private string $httpMethod,
        private string $contentType,
        private array $bodyContents,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        //
        $endpoints = (new RoutesRegistration())->endpoints();

        //
        $requestParams = new HttpRequestParams(
            $this->httpUri,
            $this->contentType,
            $this->bodyContents,
        );
        $bodyParams = $requestParams->bodyParams();
        $uriEmbeddedParam = $requestParams->uriEmbeddedParam();

        //
        $uri = $this->httpUri;
        if (in_array($this->httpMethod, ['DELETE', 'PUT', 'PATCH'])) {
            $lastSlashPos = strrpos($uri, '/');
            if ($lastSlashPos !== false) {
                $uri = substr($uri, 0, $lastSlashPos);
            }
        }

        $urlAssociatedToProxy = new UrlAssociatedToProxy(
            $uri,
            $this->httpMethod,
            $endpoints
        );

        $uriParams = $requestParams->uriParams();

        $proxy = $urlAssociatedToProxy->proxy();
        $method = $urlAssociatedToProxy->method();

        $entity = new $proxy($uriParams, $bodyParams, $method, $uriEmbeddedParam);

        echo json_encode($entity());
    }
}