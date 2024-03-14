<?php

namespace App\EntryPoint;

use App\Gun;
use App\Routing\EndpointsRegistration;
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
        $routeToEntity = new RouteToEntity(
            $this->httpUri,
            $this->httpMethod,
            $endpoints
        );

        //
        $bodyParams = $requestParams->bodyParams();
        $uriParams = $requestParams->uriParams();
        //
        $entity = $routeToEntity->entity();
        $entityMethod = $routeToEntity->entityMethod();
        $methodArguments = $routeToEntity->methodArguments();
        //
        $entity = new $entity($uriParams, $bodyParams, $entityMethod);

        echo $entity();
    }
}