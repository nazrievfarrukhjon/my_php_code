<?php

namespace App\Services;

use App\Cache\CacheInterface;
use App\DB\Contracts\DBConnection;
use App\Log\LoggerInterface;
use Exception;
use PDO;

readonly class FraudDetectionService
{
    public function __construct(
        private DBConnection $primaryDB,
        private DBConnection $replicaDB,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}

    public function checkFraud(array $clientData): array
    {
        $startTime = microtime(true);
        
        try {
            $normalizedData = $this->normalizeClientData($clientData);
            
            $cacheKey = $this->generateCacheKey($normalizedData);
            $cachedResult = $this->cache->get($cacheKey);
            
            if ($cachedResult) {
                $this->logger->info('Fraud check cache hit', ['cache_key' => $cacheKey]);
                return $this->addResponseTime($cachedResult, $startTime);
            }
            
            $result = $this->performFraudLookup($normalizedData);
            
            $this->cache->set($cacheKey, $result, 3600);
            
            $this->logger->info('Fraud check completed', [
                'is_fraud' => $result['is_fraud'],
                'confidence' => $result['confidence'],
                'sources_count' => count($result['sources'])
            ]);
            
            return $this->addResponseTime($result, $startTime);
            
        } catch (Exception $e) {
            $this->logger->error('Fraud check failed', [
                'error' => $e->getMessage(),
                'client_data' => $clientData
            ]);
            throw $e;
        }
    }

    private function normalizeClientData(array $data): array
    {
        $normalized = [];
        
        $fields = ['first_name', 'second_name', 'third_name', 'fourth_name'];
        
        foreach ($fields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $normalized[$field] = $this->normalizeUnicode($data[$field]);
                $normalized[$field] = mb_strtolower($normalized[$field], 'UTF-8');
                $normalized[$field] = preg_replace('/\s+/', ' ', trim($normalized[$field]));
            }
        }
        
        if (isset($data['birth_date'])) {
            $normalized['birth_date'] = $this->normalizeBirthDate($data['birth_date']);
        }
        
        return $normalized;
    }

    private function normalizeUnicode(string $text): string
    {
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        $text = \Normalizer::normalize($text, \Normalizer::FORM_NFC);
        
        $replacements = [
            'ё' => 'е', 'Ё' => 'Е',
            'й' => 'и', 'Й' => 'И',
            'ъ' => '', 'ь' => '',
            'ы' => 'и', 'Ы' => 'И'
        ];
        
        return strtr($text, $replacements);
    }

    /**
     * @throws Exception
     */
    private function normalizeBirthDate(string $date): string
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format('Y-m-d');
        } catch (Exception $e) {
            throw new Exception("Invalid birth date format: {$date}");
        }
    }

    private function performFraudLookup(array $data): array
    {
        $connection = $this->replicaDB->connection();
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $results = [];
        $totalConfidence = 0;
        $sources = [];
        
        if (isset($data['birth_date'])) {
            $birthYear = (int) substr($data['birth_date'], 0, 4);
            $partitionName = $this->getPartitionName($birthYear);
            
            $sql = "SELECT id, first_name, second_name, third_name, fourth_name, type, 
                           similarity(first_name, :first_name) as first_sim,
                           similarity(second_name, :second_name) as second_sim,
                           similarity(third_name, :third_name) as third_sim,
                           similarity(fourth_name, :fourth_name) as fourth_sim
                    FROM {$partitionName}
                    WHERE birth_date = :birth_date";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute([
                'first_name' => $data['first_name'] ?? '',
                'second_name' => $data['second_name'] ?? '',
                'third_name' => $data['third_name'] ?? '',
                'fourth_name' => $data['fourth_name'] ?? '',
                'birth_date' => $data['birth_date']
            ]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $confidence = $this->calculateConfidence($row, $data);
                if ($confidence > 0.3) {
                    $results[] = $row;
                    $totalConfidence += $confidence;
                    $sources[] = $row['type'] ?? 'unknown';
                }
            }
        }
        
        if (empty($results) && !empty($data['first_name'])) {
            $sql = "SELECT id, first_name, second_name, third_name, fourth_name, type, birth_date,
                           similarity(first_name, :first_name) as first_sim
                    FROM blacklists
                    WHERE first_name % :first_name
                    ORDER BY similarity(first_name, :first_name) DESC
                    LIMIT 10";
            
            $stmt = $connection->prepare($sql);
            $stmt->execute(['first_name' => $data['first_name']]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $confidence = $this->calculateConfidence($row, $data);
                if ($confidence > 0.5) {
                    $results[] = $row;
                    $totalConfidence += $confidence;
                    $sources[] = $row['type'] ?? 'unknown';
                }
            }
        }
        
        $isFraud = !empty($results);
        $avgConfidence = $isFraud ? ($totalConfidence / count($results)) : 0;
        
        return [
            'is_fraud' => $isFraud,
            'confidence' => $avgConfidence,
            'sources' => array_unique($sources),
            'matches' => $results
        ];
    }

    private function calculateConfidence(array $dbRow, array $inputData): float
    {
        $confidence = 0;
        $totalFields = 0;
        
        $fields = ['first_name', 'second_name', 'third_name', 'fourth_name'];
        
        foreach ($fields as $field) {
            if (!empty($inputData[$field]) && !empty($dbRow[$field])) {
                $similarity = $dbRow["{$field}_sim"] ?? 0;
                $confidence += $similarity;
                $totalFields++;
            }
        }
        
        if (isset($inputData['birth_date']) &&
            isset($dbRow['birth_date']) && 
            $inputData['birth_date'] === $dbRow['birth_date']) {
            $confidence += 0.2;
        }
        
        return $totalFields > 0 ? ($confidence / $totalFields) : 0;
    }

    private function getPartitionName(int $year): string
    {
        $decade = intval($year / 10) * 10;
        return "blacklists_{$decade}_" . ($decade + 9);
    }

    private function generateCacheKey(array $data): string
    {
        $keyData = array_filter($data);
        return 'fraud_check_' . md5(serialize($keyData));
    }

    private function addResponseTime(array $result, float $startTime): array
    {
        $result['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);
        return $result;
    }

    public function bulkFraudCheck(array $clients): array
    {
        $results = [];
        
        foreach ($clients as $index => $client) {
            try {
                $results[$index] = $this->checkFraud($client);
            } catch (Exception $e) {
                $results[$index] = [
                    'is_fraud' => false,
                    'confidence' => 0,
                    'sources' => [],
                    'error' => $e->getMessage(),
                    'response_time_ms' => 0
                ];
            }
        }
        
        return $results;
    }
}
