<?php

namespace App\EntryPoints\Http;

use App\Container\Container;
use App\DB\Contracts\DBConnection;
use App\Log\LoggerInterface;
use App\Routing\RoutesRegistration;
use App\Routing\UrlAssociatedToController;
use Exception;

readonly class WebRequest
{

    public function __construct(
        private HttpUri         $httpUri,
        private string          $httpMethod,
        private string          $contentType,
        private array           $bodyContents,
        private DBConnection        $db,
        private LoggerInterface $logger,
        private Container $container,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $routes = (new RoutesRegistration($this->container))->getRoutes();

        $requestParser = new RequestParser(
            $this->httpUri->cleanUri(),
            $this->contentType,
            $this->bodyContents,
            strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET')),
        );

        $parsedParams = $requestParser->parseParams();
        $bodyParams = $parsedParams['body'];
        $uriParams = $parsedParams['uri'];

        $request = [
            'uriParams' => $uriParams,
            'bodyParams' => $bodyParams,
            'uriEmbeddedParam' => $this->httpUri->uriEmbeddedParam()
        ];

        $associatedUrlToController = new UrlAssociatedToController(
            $this->httpUri->cleanUri(),
            $this->httpMethod,
            $routes,
            $this->logger,
        );

        try {
            $response = $associatedUrlToController->handleRequest($request, $this->container);
            echo json_encode($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
