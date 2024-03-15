<?php

namespace App\EntryPoint;

use App\Gun;
use App\Routing\EndpointsRegistration;
use App\Routing\UrlProxy;
use Exception;

readonly class HttpRequest
{
    public function __construct(
        private string $httpUri,
        private string $httpMethod,
        private string $contentType,
        private string $content,
    ) {}

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $endpoints = (new EndpointsRegistration())->endpoints();

        //////////////////////////////// params
        $requestParams = new HttpRequestParams(
            $this->httpUri,
            $this->contentType,
            $this->content,
        );
        //////////////////////////////////// uri
        $urlProxy = new UrlProxy(
            $this->httpUri,
            $this->httpMethod,
            $endpoints
        );

        //
        $bodyParams = $requestParams->bodyParams();
        $uriParams = $requestParams->uriParams();
        //
        $proxy = $urlProxy->proxy();
        $method = $urlProxy->method();
        //$methodArguments = $routeToEntity->methodArguments();
        //
        $entity = new $proxy($uriParams, $bodyParams, $method);

        echo $entity();
    }
}