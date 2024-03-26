<?php

namespace App\EntryPoint;

use Exception;

readonly class HttpRequestParams
{
    public function __construct(
        private string $httpUri,
        private string $contentType,
        private string $content
    ) {}

    /**
     * @throws Exception
     */
    public function bodyParams(): array
    {
        $body = $this->content;

        if ($this->contentType === "application/json") {
            if ($body) {
                return json_decode($body, true);
            }

            return [];
        } elseif ($this->contentType === "application/x-www-form-urlencoded") {
            $result = [];
            parse_str($body, $result);
            return $result;
        } elseif ($this->contentType === '') {
            return [];
        } else {
            throw new Exception('http content type problem');
        }
    }

    public function uriParams(): array
    {
        if (str_contains($this->httpUri, '?')) {
            $explodedUri = explode('?', $this->httpUri);
            $queryString = $explodedUri[1];
            $params = explode('&', $queryString);
            $queryParams = [];
            foreach ($params as $param) {
                list($key, $value) = explode('=', $param);
                $queryParams[$key] = $value;
            }

            return $queryParams;
        }

        return [];
    }


}