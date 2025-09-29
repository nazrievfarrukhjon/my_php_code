<?php

namespace App\Middlewares;

use App\Http\RequestDTO;
use App\Log\LoggerInterface;

readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(RequestDTO $request, callable $next)
    {
        $this->logger->info("Incoming request: " . json_encode($request));

        $response = $next($request);

        $this->logger->info("Response: " . json_encode($response));

        return $response;
    }
}
