<?php

namespace App\Monitoring;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;
use Prometheus\Counter;
use Prometheus\Histogram;
use Prometheus\Gauge;

class PrometheusMetrics
{
    private CollectorRegistry $registry;
    private Counter $fraudChecksTotal;
    private Counter $fraudDetectedTotal;
    private Histogram $fraudCheckDuration;
    private Histogram $databaseQueryDuration;
    private Gauge $activeConnections;
    private Gauge $queueSize;
    private Counter $cacheHits;
    private Counter $cacheMisses;
    private Counter $elasticsearchQueries;
    private Counter $rabbitmqMessages;

    public function __construct(string $redisHost = 'localhost', int $redisPort = 6379)
    {
        $redis = new Redis(['host' => $redisHost, 'port' => $redisPort]);
        $this->registry = new CollectorRegistry($redis);
        
        $this->initializeMetrics();
    }

    private function initializeMetrics(): void
    {
        $this->fraudChecksTotal = $this->registry->getOrRegisterCounter(
            'fraud_detection',
            'checks_total',
            'Total number of fraud checks performed',
            ['status', 'source']
        );

        $this->fraudDetectedTotal = $this->registry->getOrRegisterCounter(
            'fraud_detection',
            'fraud_detected_total',
            'Total number of fraud cases detected',
            ['confidence_level', 'source']
        );

        $this->fraudCheckDuration = $this->registry->getOrRegisterHistogram(
            'fraud_detection',
            'check_duration_seconds',
            'Time spent on fraud checks',
            ['method'],
            [0.1, 0.25, 0.5, 1.0, 2.5, 5.0, 10.0]
        );

        $this->databaseQueryDuration = $this->registry->getOrRegisterHistogram(
            'database',
            'query_duration_seconds',
            'Time spent on database queries',
            ['operation', 'table'],
            [0.01, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0]
        );

        $this->activeConnections = $this->registry->getOrRegisterGauge(
            'system',
            'active_connections',
            'Number of active connections',
            ['type']
        );

        $this->queueSize = $this->registry->getOrRegisterGauge(
            'queue',
            'size',
            'Number of messages in queue',
            ['queue_name']
        );

        $this->cacheHits = $this->registry->getOrRegisterCounter(
            'cache',
            'hits_total',
            'Total number of cache hits',
            ['cache_type', 'key_prefix']
        );

        $this->cacheMisses = $this->registry->getOrRegisterCounter(
            'cache',
            'misses_total',
            'Total number of cache misses',
            ['cache_type', 'key_prefix']
        );

        $this->elasticsearchQueries = $this->registry->getOrRegisterCounter(
            'elasticsearch',
            'queries_total',
            'Total number of Elasticsearch queries',
            ['operation', 'index']
        );

        $this->rabbitmqMessages = $this->registry->getOrRegisterCounter(
            'rabbitmq',
            'messages_total',
            'Total number of RabbitMQ messages',
            ['operation', 'queue']
        );
    }

    public function recordFraudCheck(string $status, string $source, float $duration, bool $isFraud, float $confidence): void
    {
        $this->fraudChecksTotal->inc(['status' => $status, 'source' => $source]);
        $this->fraudCheckDuration->observe($duration, ['method' => 'sync']);

        if ($isFraud) {
            $confidenceLevel = $this->getConfidenceLevel($confidence);
            $this->fraudDetectedTotal->inc(['confidence_level' => $confidenceLevel, 'source' => $source]);
        }
    }

    public function recordDatabaseQuery(string $operation, string $table, float $duration): void
    {
        $this->databaseQueryDuration->observe($duration, ['operation' => $operation, 'table' => $table]);
    }

    public function recordCacheHit(string $cacheType, string $keyPrefix = 'default'): void
    {
        $this->cacheHits->inc(['cache_type' => $cacheType, 'key_prefix' => $keyPrefix]);
    }

    public function recordCacheMiss(string $cacheType, string $keyPrefix = 'default'): void
    {
        $this->cacheMisses->inc(['cache_type' => $cacheType, 'key_prefix' => $keyPrefix]);
    }

    public function recordElasticsearchQuery(string $operation, string $index): void
    {
        $this->elasticsearchQueries->inc(['operation' => $operation, 'index' => $index]);
    }

    public function recordRabbitMQMessage(string $operation, string $queue): void
    {
        $this->rabbitmqMessages->inc(['operation' => $operation, 'queue' => $queue]);
    }

    public function updateActiveConnections(string $type, int $count): void
    {
        $this->activeConnections->set($count, ['type' => $type]);
    }

    public function updateQueueSize(string $queueName, int $size): void
    {
        $this->queueSize->set($size, ['queue_name' => $queueName]);
    }

    private function getConfidenceLevel(float $confidence): string
    {
        if ($confidence >= 0.9) return 'high';
        if ($confidence >= 0.7) return 'medium';
        if ($confidence >= 0.5) return 'low';
        return 'very_low';
    }

    public function getMetrics(): string
    {
        $renderer = new \Prometheus\RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }

    public function getMetricsSummary(): array
    {
        $samples = $this->registry->getMetricFamilySamples();
        $summary = [];

        foreach ($samples as $metricFamily) {
            $name = $metricFamily->getName();
            $type = $metricFamily->getType();
            
            foreach ($metricFamily->getSamples() as $sample) {
                $labels = $sample->getLabelNames();
                $values = $sample->getLabelValues();
                
                $key = $name;
                if (!empty($labels)) {
                    $key .= '_' . implode('_', array_map(fn($l, $v) => $l . '_' . $v, $labels, $values));
                }
                
                $summary[$key] = [
                    'name' => $name,
                    'value' => $sample->getValue(),
                    'labels' => array_combine($labels, $values),
                    'type' => $type
                ];
            }
        }

        return $summary;
    }

    public function recordProfilingData(array $data): void
    {
        foreach ($data as $operation => $metrics) {
            if (isset($metrics['duration'])) {
                $this->databaseQueryDuration->observe(
                    $metrics['duration'],
                    ['operation' => $operation, 'table' => $metrics['table'] ?? 'unknown']
                );
            }
        }
    }

    public function getPerformanceInsights(): array
    {
        $summary = $this->getMetricsSummary();
        
        $insights = [
            'fraud_check_performance' => [
                'avg_duration' => $this->getAverageMetric($summary, 'fraud_detection_check_duration_seconds'),
                'total_checks' => $this->getTotalMetric($summary, 'fraud_detection_checks_total'),
                'fraud_rate' => $this->calculateFraudRate($summary)
            ],
            'cache_performance' => [
                'hit_rate' => $this->calculateCacheHitRate($summary),
                'total_hits' => $this->getTotalMetric($summary, 'cache_hits_total'),
                'total_misses' => $this->getTotalMetric($summary, 'cache_misses_total')
            ],
            'database_performance' => [
                'avg_query_time' => $this->getAverageMetric($summary, 'database_query_duration_seconds'),
                'slowest_operation' => $this->getSlowestOperation($summary)
            ]
        ];

        return $insights;
    }

    private function getAverageMetric(array $summary, string $prefix): float
    {
        $values = array_filter($summary, fn($k) => str_starts_with($k, $prefix), ARRAY_FILTER_USE_KEY);
        return empty($values) ? 0 : array_sum(array_column($values, 'value')) / count($values);
    }

    private function getTotalMetric(array $summary, string $prefix): int
    {
        $values = array_filter($summary, fn($k) => str_starts_with($k, $prefix), ARRAY_FILTER_USE_KEY);
        return (int) array_sum(array_column($values, 'value'));
    }

    private function calculateFraudRate(array $summary): float
    {
        $totalChecks = $this->getTotalMetric($summary, 'fraud_detection_checks_total');
        $fraudDetected = $this->getTotalMetric($summary, 'fraud_detection_fraud_detected_total');
        
        return $totalChecks > 0 ? ($fraudDetected / $totalChecks) * 100 : 0;
    }

    private function calculateCacheHitRate(array $summary): float
    {
        $hits = $this->getTotalMetric($summary, 'cache_hits_total');
        $misses = $this->getTotalMetric($summary, 'cache_misses_total');
        $total = $hits + $misses;
        
        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    private function getSlowestOperation(array $summary): string
    {
        $dbMetrics = array_filter($summary, fn($k) => str_starts_with($k, 'database_query_duration_seconds'), ARRAY_FILTER_USE_KEY);
        
        if (empty($dbMetrics)) {
            return 'No data';
        }
        
        $maxValue = max(array_column($dbMetrics, 'value'));
        $slowest = array_filter($dbMetrics, fn($m) => $m['value'] === $maxValue);
        
        return array_key_first($slowest) ?? 'Unknown';
    }
}
