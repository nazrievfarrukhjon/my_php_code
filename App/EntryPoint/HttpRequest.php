<?php

namespace App\EntryPoint;

use App\Routing\RoutesRegistration;
use App\Routing\UrlAssociatedToProxy;
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
        $endpoints = (new RoutesRegistration())->endpoints();

        //////////////////////////////// params
        $requestParams = new HttpRequestParams(
            $this->httpUri,
            $this->contentType,
            $this->content,
        );
        //////////////////////////////////// uri
        $urlAssociatedToProxy = new UrlAssociatedToProxy(
            $this->httpUri,
            $this->httpMethod,
            $endpoints
        );

        //
        $bodyParams = $requestParams->bodyParams();
        $uriParams = $requestParams->uriParams();
        //
        $proxy = $urlAssociatedToProxy->proxy();
        $method = $urlAssociatedToProxy->method();

        $entity = new $proxy($uriParams, $bodyParams, $method);

        echo json_encode($entity());
    }
}