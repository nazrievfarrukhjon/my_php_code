<?php


use App\Integration\Incoming\Http\IncomingHttpRequestHandler;

try {
    IncomingHttpRequestHandler::new()->handle();
} catch (Exception $e) {
    $errorResponse = [
        'error' => $e
    ];
    echo json_encode($errorResponse);
}
