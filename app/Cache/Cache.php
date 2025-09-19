<?php

namespace App\Cache;

//implement psr-16 cache interface
class Cache
{

    const ENDPOINT_FILE_PATH = __DIR__ . '/../../conf/endpoints.php';

    public function endpoints(): array {
        if (file_exists(static::ENDPOINT_FILE_PATH) && is_readable(static::ENDPOINT_FILE_PATH)) {
            $endpoints = include static::ENDPOINT_FILE_PATH;

            if (is_array($endpoints)) {
                return $endpoints;
            }
        }
        return [];
    }

    public function storeEndpoints(array $endpoints): void
    {
        $serializedEndpoints = $endpoints;

        file_put_contents(static::ENDPOINT_FILE_PATH, '<?php return ' . var_export($serializedEndpoints, true) . ';');
    }

    public function cleanEndpoints(): void
    {
        file_put_contents(static::ENDPOINT_FILE_PATH, '<?php return ' . var_export([], true) . ';');
    }


}