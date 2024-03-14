<?php

namespace App\EntryPoint;

use Exception;

readonly class RouteToEntity
{
    public function __construct(
        private string $httpUri,
        private string $httMethod,
        private array  $endpoints,
    ) {}

    /**
     * @throws Exception
     */
    public function entity(): string
    {
        if (isset($this->endpoints[$this->httMethod][$this->httpUri])) {
            return $this->endpoints[$this->httMethod][$this->httpUri][0];
        }

        throw new Exception('route not found class');
    }

    /**
     * @throws Exception
     */
    public function entityMethod(): string
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