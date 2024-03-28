<?php

namespace App\EntryPoints\Http;

use App\Routing\RoutesRegistration;
use App\Routing\UrlAssociatedToProxy;
use Exception;

readonly class MyHttpRequest
{
    public function __construct(
        private HttpUri $httpUri,
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
            $this->httpUri->cleanedUri(),
            $this->contentType,
            $this->bodyContents,
        );
        $bodyParams = $requestParams->bodyParams();
        //
        $urlAssociatedToProxy = new UrlAssociatedToProxy(
            $this->httpUri->cleanedUri(),
            $this->httpMethod,
            $endpoints
        );

        $uriParams = $requestParams->uriParams();

        $proxy = $urlAssociatedToProxy->proxy();
        $method = $urlAssociatedToProxy->method();

        $entity = new $proxy(
            $uriParams,
            $bodyParams,
            $method,
            $this->httpUri->uriEmbeddedParam()
        );

        echo json_encode($entity());
    }
}