<?php

namespace App\Routing;

use Exception;

readonly class UrlAssociatedToProxy
{
    public function __construct(
        private string $httpUri,
        private string $httMethod,
        private array  $endpoints,
    ) {}

    /**
     * @throws Exception
     */
    public function proxy(): string
    {
        // get by http method ans uri the endpoint class which will handle request
        if (isset($this->endpoints[$this->httMethod][$this->httpUri])) {
            return $this->endpoints[$this->httMethod][$this->httpUri][0];
        }

        throw new Exception('endpoint not found class');
    }

    /**
     * @throws Exception
     */
    public function method(): string
    {
        if (isset($this->endpoints[$this->httMethod][$this->httpUri])) {
            return $this->endpoints[$this->httMethod][$this->httpUri][1];
        }

        throw new Exception('route not found method');
    }

    public function methodArguments(): array
    {
        if (isset($this->endpoints[$this->httMethod][$this->httpUri])) {
            return $this->endpoints[$this->httMethod][$this->httpUri][2];
        }

        throw new Exception('route not found arguments');
    }

}