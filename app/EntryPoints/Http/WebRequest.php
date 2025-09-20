<?php

namespace App\EntryPoints\Http;

use App\DB\Postgres;
use App\Dispatcher;
use App\Log\LoggerInterface;
use App\Routing\Router;
use App\Routing\RoutesRegistration;
use Exception;

readonly class WebRequest
{
    public function __construct(
        private HttpUri         $httpUri,
        private string          $httpMethod,
        private string          $contentType,
        private array           $bodyContents,
        private Postgres        $myDb,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        // 1
        $endpoints = (new RoutesRegistration())->endpoints();

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
        $router = new Router($endpoints);
        $route = $router->match($this->httpUri->cleanUri(), $this->httpMethod);
        $controllerClass = $route['controller'];
        $method = $route['method'];

        // 4
        $dispatcher = new Dispatcher($this->myDb, $this->logger);

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
