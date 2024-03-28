<?php

namespace App\Routing;

use Exception;

readonly class UrlAssociatedToProxy
{
    public function __construct(
        private string $httpUri,
        private string $httpMethod,
        private array  $endpoints,
    ) {}

    /**
     * @throws Exception
     */
    public function proxy(): string
    {
        if (isset($this->endpoints[$this->httpMethod][$this->httpUri])) {
            return $this->endpoints[$this->httpMethod][$this->httpUri][0];
        }

        throw new Exception('Endpoint not found');
    }

    /**
     * @throws Exception
     */
    public function method(): string
    {
        if (isset($this->endpoints[$this->httpMethod][$this->httpUri])) {
            return $this->endpoints[$this->httpMethod][$this->httpUri][1];
        }

        throw new Exception('route method not found');
    }

    /**
     * @throws Exception
     */
    public function methodArguments(): array
    {
        if (isset($this->endpoints[$this->httpMethod][$this->httpUri])) {
            return $this->endpoints[$this->httpMethod][$this->httpUri][2];
        }

        throw new Exception('route not found arguments');
    }

}