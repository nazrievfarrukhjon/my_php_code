<?php

namespace App\EntryPoints\Http;

use App\DB\MyDB;
use App\Log\LoggerInterface;
use App\Routing\RoutesRegistration;
use App\Routing\UrlAssociatedToController;
use Exception;

//todo split into Router vs Dispatcher.
readonly class MyHttpRequest
{
    public function __construct(
        private HttpUri $httpUri,
        private string $httpMethod,
        private string $contentType,
        private array $bodyContents,
        private MyDB $myDb,
        private LoggerInterface $logger
    ) {}

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        // Register routes
        $endpoints = (new RoutesRegistration())->endpoints();

        // Parse request params
        $requestParams = new HttpRequestParams(
            $this->httpUri->cleanedUri(),
            $this->contentType,
            $this->bodyContents,
        );

        $bodyParams = $requestParams->bodyParams();
        $uriParams = $requestParams->uriParams();

        // Map URL to proxy class + method
        $urlAssociatedToController = new UrlAssociatedToController(
            $this->httpUri->cleanedUri(),
            $this->httpMethod,
            $endpoints
        );

        $controllerClass = $urlAssociatedToController->getController();
        $method = $urlAssociatedToController->method();

        // Inject dependencies into proxy (MyDB, Env, Logger, etc.)
        $proxy = new $controllerClass(
            $uriParams,
            $bodyParams,
            $method,
            $this->httpUri->uriEmbeddedParam(),
            $this->myDb,      // injected
            $this->logger     // injected
        );

        try {
            $response = $proxy(); // execute proxy method
            echo json_encode($response);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
