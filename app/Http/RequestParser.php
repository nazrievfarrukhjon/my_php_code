<?php

namespace App\Http;

use Exception;

readonly class RequestParser
{
    public function __construct(
        private string $httpUri,
        private string $contentType,
        private array  $bodyContents,
        private string $httpMethod,
    )
    {
    }

    /**
     * @throws Exception
     */
    private function parseBodyParams(): array
    {
        if ($this->contentType === "application/json") {
            if ($this->bodyContents['file_get_contents']) {
                return json_decode($this->bodyContents['file_get_contents'], true);
            }

            return [];
        } elseif ($this->contentType === "multipart/form-data") {
            if ($this->httpMethod !== 'GET') {
                return $this->explodeBodies();
            }
            throw new Exception('multipart/form-data with get method not allowed');

        } elseif ($this->contentType === "application/x-www-form-urlencoded") {
            $result = [];
            parse_str($this->bodyContents['file_get_contents'], $result);
            return $result;
        } elseif ($this->contentType === '') {
            return [];
        } else {
            throw new Exception('http content type problem');
        }
    }

    private function explodeBodies(): array
    {
        $bodyParams = [];

        foreach ($this->bodyContents['post'] as $key => $value) {
            $bodyParams[$key] = $value;
        }

        foreach ($this->bodyContents['files'] as $key => $file) {
            $bodyParams[$key] = $file;
        }

        if (isset($this->bodyContents['file_get_contents'])) {
            $file_get_contents = json_decode($this->bodyContents['file_get_contents'], true);
            foreach ($file_get_contents as $key => $file) {
                $bodyParams[$key] = $file;
            }
        }

        return $bodyParams;
    }

    // uri divided by ?
    private function parseUriParams(): array
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

    /**
     * @throws Exception
     */
    public function parseParams(): array
    {
        return [
            'body' => $this->parseBodyParams(),
            'uri' => $this->parseUriParams(),
        ];


    }


}