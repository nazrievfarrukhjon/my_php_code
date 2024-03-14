<?php

namespace App\EntryPoint;

use Exception;

readonly class HttpRequestParams
{
    public function __construct(
        private string $httpUri,
        private string $contentType,
        private string $content
    ){}

    /**
     * @throws Exception
     */
    public function bodyParams(): array
    {
        $body = $this->content;

        if ($this->contentType === "application/json") {
            if($body) {
                return json_decode($body, true);
            }

            return [];
        } elseif ($this->contentType === "application/x-www-form-urlencoded") {
            /*
             * parse_str
             *  If the second parameter arr is present,
             *  variables are stored in this variable as array elements instead.
             *  Since 7.2.0 this parameter is not optional.
             */
            $result = [];
            parse_str($body, $result);
            return $result;
        } else {
            throw new Exception('http content type problem');
        }
    }

    public function uriParams(): array
    {
        if (str_contains($this->httpUri, '?')) {
            $explodedUri = explode('?', $this->httpUri);
            // Get the query string part
            $queryString = $explodedUri[1];

            // Explode the query string to get individual parameters
            $params = explode('&', $queryString);

            // Initialize an empty array to store key-value pairs
            $queryParams = [];

            // Loop through each parameter and split key-value pairs
            foreach ($params as $param) {
                list($key, $value) = explode('=', $param);
                $queryParams[$key] = $value;
            }

            return $queryParams;
        }

        return [];
    }


}