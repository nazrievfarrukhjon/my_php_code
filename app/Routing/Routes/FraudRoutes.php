<?php

namespace App\Routing\Routes;

use App\Controllers\FraudController;

class FraudRoutes
{
    public static function getRoutes(): array
    {
        return [
            'POST' => [
                '/fraud/check' => [
                    'controller' => FraudController::class,
                    'method' => 'checkFraud',
                    'middlewares' => []
                ],
                '/fraud/check-async' => [
                    'controller' => FraudController::class,
                    'method' => 'checkFraudAsync',
                    'middlewares' => []
                ],
                '/fraud/bulk-check' => [
                    'controller' => FraudController::class,
                    'method' => 'bulkCheckFraud',
                    'middlewares' => []
                ]
            ],
            'GET' => [
                '/fraud/result/{correlation_id}' => [
                    'controller' => FraudController::class,
                    'method' => 'getFraudCheckResult',
                    'middlewares' => []
                ],
                '/fraud/stats' => [
                    'controller' => FraudController::class,
                    'method' => 'getFraudStats',
                    'middlewares' => []
                ]
            ]
        ];
    }
}
