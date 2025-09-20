<?php

namespace App\EntryPoints\Http;

use App\Container\Container;
use App\DB\DatabaseConnectionInterface;
use App\Dispatcher;
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
        private DatabaseConnectionInterface        $db,
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
        // 1
        $endpoints = (new RoutesRegistration($this->container))->endpoints();

        // 2
        $requestParser = new RequestParser(
            $this->httpUri->cleanUri(),
            $this->contentType,
            $this->bodyContents,
            strtoupper(trim($_SERVER['REQUEST_METHOD'] ?? 'GET')),
        );

        $params = $requestParser->parse();
        $bodyParams = $params['body'];
        $uriParams = $params['uri'];


        // 3
        $urlAssociatedToController = new UrlAssociatedToController(
            $this->httpUri->cleanUri(),
            $this->httpMethod,
            $endpoints,
            $this->logger,
        );
        $cm = $urlAssociatedToController->getControllerWithMethod();
        $controllerClass = $cm['controller'];
        $method = $cm['method'];

        // 4
        $dispatcher = new Dispatcher($this->db, $this->logger);

        try {
            $response = $dispatcher->dispatch(
                $controllerClass,
                $method,
                $uriParams,
                $bodyParams,
                $this->httpUri->uriEmbeddedParam()
            );
            echo json_encode($response);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

    }
}
