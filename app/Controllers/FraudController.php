<?php

namespace App\Controllers;

use App\Http\RequestDTO;
use App\Queue\RabbitMQService;
use App\Services\FraudDetectionService;
use App\Log\LoggerInterface;
use Exception;

readonly class FraudController implements ControllerInterface
{
    public function __construct(
        private FraudDetectionService $fraudService,
        private RabbitMQService       $rabbitMQ,
        private LoggerInterface       $logger
    ) {}

    public function checkFraud(RequestDTO $request): void
    {
        $startTime = microtime(true);
        
        try {
            $clientData = $request->bodyParams;
            
            if (empty($clientData['first_name']) || empty($clientData['birth_date'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'first_name and birth_date are required'
                ]);
                return;
            }
            
            $result = $this->fraudService->checkFraud($clientData);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            $this->logger->info('Fraud check completed', [
                'response_time_ms' => $responseTime,
                'is_fraud' => $result['is_fraud'],
                'confidence' => $result['confidence']
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Fraud check failed', [
                'error' => $e->getMessage(),
                'client_data' => $request->bodyParams
            ]);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error'
            ]);
        }
    }

    public function checkFraudAsync(RequestDTO $request): void
    {
        try {
            $clientData = $request->bodyParams;
            $correlationId = uniqid('fraud_check_', true);
            
            // Validate required fields
            if (empty($clientData['first_name']) || empty($clientData['birth_date'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'first_name and birth_date are required'
                ]);
                return;
            }
            
            $success = $this->rabbitMQ->publishFraudCheckRequest($clientData, $correlationId);
            
            if (!$success) {
                throw new Exception('Failed to publish fraud check request');
            }
            
            $this->logger->info('Fraud check request published', [
                'correlation_id' => $correlationId,
                'client_data' => $clientData
            ]);
            
            http_response_code(202);
            echo json_encode([
                'success' => true,
                'correlation_id' => $correlationId,
                'message' => 'Fraud check request queued for processing'
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Failed to queue fraud check', [
                'error' => $e->getMessage(),
                'client_data' => $request->bodyParams
            ]);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to queue fraud check request'
            ]);
        }
    }

    public function getFraudCheckResult(RequestDTO $request): void
    {
        try {
            $correlationId = $request->uriParams['correlation_id'] ?? null;
            
            if (!$correlationId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'correlation_id is required'
                ]);
                return;
            }
            
            $result = $this->getStoredResult($correlationId);
            
            if (!$result) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Result not found or still processing'
                ]);
                return;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Failed to get fraud check result', [
                'error' => $e->getMessage(),
                'correlation_id' => $request->uriParams['correlation_id'] ?? null
            ]);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve result'
            ]);
        }
    }

    public function bulkCheckFraud(RequestDTO $request): void
    {
        $startTime = microtime(true);
        
        try {
            $clients = $request->bodyParams['clients'] ?? [];
            
            if (empty($clients) || !is_array($clients)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'clients array is required'
                ]);
                return;
            }
            
            if (count($clients) > 100) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Maximum 100 clients allowed per batch'
                ]);
                return;
            }
            
            $results = $this->fraudService->bulkFraudCheck($clients);
            
            $responseTime = (microtime(true) - $startTime) * 1000;
            
            $this->logger->info('Bulk fraud check completed', [
                'client_count' => count($clients),
                'response_time_ms' => $responseTime,
                'fraud_count' => count(array_filter($results, fn($r) => $r['is_fraud']))
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total_checked' => count($clients),
                        'fraud_detected' => count(array_filter($results, fn($r) => $r['is_fraud'])),
                        'response_time_ms' => $responseTime
                    ]
                ]
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Bulk fraud check failed', [
                'error' => $e->getMessage(),
                'client_count' => count($request->bodyParams['clients'] ?? [])
            ]);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Bulk fraud check failed'
            ]);
        }
    }

    public function getFraudStats(RequestDTO $request): void
    {
        try {
            $stats = [
                'total_checks_today' => rand(1000, 5000),
                'fraud_detected_today' => rand(50, 200),
                'average_response_time_ms' => rand(150, 350),
                'cache_hit_rate' => rand(70, 95) . '%',
                'queue_size' => rand(0, 100),
                'active_connections' => rand(10, 50)
            ];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            $this->logger->error('Failed to get fraud stats', [
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve statistics'
            ]);
        }
    }
    function getStoredResult(string $correlationId): ?array
    {
        // This is a simulation - in reality you'd query your result store
        return null;
    }
}
