<?php

namespace App\Controllers;

use App\Log\Logger;
use OpenApi\Generator;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Documentation",
 *     description="API documentation endpoints"
 * )
 */
class SwaggerController implements ControllerInterface
{
    private Logger $logger;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
    }

    /**
     * @OA\Get(
     *     path="/api/docs",
     *     summary="Swagger UI",
     *     description="Interactive API documentation interface for all services",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="Swagger UI HTML page",
     *         @OA\MediaType(mediaType="text/html")
     *     )
     * )
     */
    public function getCompleteSwaggerUI($request): array
    {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/api/docs.json",
                dom_id: "#swagger-ui",
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                tryItOutEnabled: true,
                requestInterceptor: (req) => {
                    return req;
                }
            });
        };
    </script>
</body>
</html>';
        
        return [
            'content_type' => 'text/html',
            'body' => $html
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/docs.json",
     *     summary="OpenAPI Specification",
     *     description="Complete OpenAPI specification for all services",
     *     tags={"Documentation"},
     *     @OA\Response(
     *         response=200,
     *         description="OpenAPI JSON specification",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function getCompleteOpenApiSpec($request): array
    {
        try {
            // Scan all controller files for OpenAPI annotations
            $openapi = Generator::scan([
                __DIR__ . '/ElasticsearchController.php',
                __DIR__ . '/GeneralElasticsearchController.php',
                __DIR__ . '/AuthController.php',
                __DIR__ . '/BlacklistController.php',
                __DIR__ . '/WhitelistController.php',
                __DIR__ . '/DriverController.php',
                __DIR__ . '/RideController.php',
                __DIR__ . '/BillingController.php',
                __DIR__ . '/WelcomeController.php',
                __DIR__ . '/SwaggerController.php'
            ]);

            return [
                'content_type' => 'application/json',
                'body' => $openapi->toJson()
            ];
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate OpenAPI spec', ['error' => $e->getMessage()]);
            return [
                'content_type' => 'application/json',
                'body' => json_encode(['error' => 'Failed to generate OpenAPI specification'])
            ];
        }
    }
}
