<?php

namespace App\Queue;

use App\Log\LoggerInterface;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;


class RabbitMQService
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
        private LoggerInterface $logger
    ) {}

    private function connect(): void
    {
        if ($this->connection === null) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->user,
                    $this->password,
                    $this->vhost
                );
                
                $this->channel = $this->connection->channel();
                
                $this->logger->info('RabbitMQ connection established', [
                    'host' => $this->host,
                    'port' => $this->port,
                    'vhost' => $this->vhost
                ]);
            } catch (Exception $e) {
                $this->logger->error('Failed to connect to RabbitMQ', [
                    'error' => $e->getMessage(),
                    'host' => $this->host
                ]);
                throw $e;
            }
        }
    }

    public function publish(string $queueName, array $data, array $options = []): bool
    {
        try {
            $this->connect();
            
            $this->channel->queue_declare($queueName, false, true, false, false);
            
            $message = new AMQPMessage(
                json_encode($data),
                array_merge([
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'content_type' => 'application/json'
                ], $options)
            );
            
            $this->channel->basic_publish($message, '', $queueName);
            
            $this->logger->info('Message published to queue', [
                'queue' => $queueName,
                'data_size' => strlen(json_encode($data))
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->logger->error('Failed to publish message', [
                'queue' => $queueName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function consume(string $queueName, callable $callback, int $maxMessages = 0): void
    {
        try {
            $this->connect();
            
            $this->channel->queue_declare($queueName, false, true, false, false);
            
            $this->channel->basic_qos(null, 1, null);
            
            $messageCount = 0;
            
            $consumerCallback = function (AMQPMessage $message) use ($callback, &$messageCount, $maxMessages) {
                try {
                    $data = json_decode($message->getBody(), true);
                    
                    $this->logger->info('Processing message from queue', [
                        'queue' => $message->getRoutingKey(),
                        'message_id' => $message->getMessageId()
                    ]);
                    
                    $result = $callback($data);
                    
                    $message->ack();
                    
                    $messageCount++;
                    
                    $this->logger->info('Message processed successfully', [
                        'message_id' => $message->getMessageId(),
                        'result' => $result
                    ]);

                    if ($maxMessages > 0 && $messageCount >= $maxMessages) {
                        $this->channel->basic_cancel($message->getDeliveryTag());
                    }
                    
                } catch (Exception $e) {
                    $this->logger->error('Failed to process message', [
                        'error' => $e->getMessage(),
                        'message_body' => $message->getBody()
                    ]);
                    
                    $message->nack(false, true);
                }
            };
            
            $this->channel->basic_consume($queueName, '', false, false, false, false, $consumerCallback);
            
            $this->logger->info('Started consuming from queue', ['queue' => $queueName]);
            
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
            
        } catch (Exception $e) {
            $this->logger->error('Failed to consume from queue', [
                'queue' => $queueName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function publishFraudCheckRequest(array $clientData, string $correlationId = null): bool
    {
        $data = [
            'type' => 'fraud_check_request',
            'client_data' => $clientData,
            'correlation_id' => $correlationId ?? uniqid(),
            'timestamp' => time()
        ];
        
        return $this->publish('fraud_check_queue', $data);
    }

    public function publishFraudCheckResponse(array $result, string $correlationId): bool
    {
        $data = [
            'type' => 'fraud_check_response',
            'result' => $result,
            'correlation_id' => $correlationId,
            'timestamp' => time()
        ];
        
        return $this->publish('fraud_check_responses', $data);
    }

    public function consumeFraudCheckRequests(callable $processor): void
    {
        $this->consume('fraud_check_queue', $processor);
    }

    public function consumeFraudCheckResponses(callable $processor): void
    {
        $this->consume('fraud_check_responses', $processor);
    }

    public function getQueueStats(string $queueName): array
    {
        try {
            $this->connect();
            
            $this->channel->queue_declare($queueName, false, true, false, false);
            
            return [
                'queue_name' => $queueName,
                'status' => 'active',
                'connection_status' => $this->connection ? 'connected' : 'disconnected'
            ];
        } catch (Exception $e) {
            $this->logger->error('Failed to get queue stats', [
                'queue' => $queueName,
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    public function close(): void
    {
        if ($this->channel) {
            $this->channel->close();
        }
        
        if ($this->connection) {
            $this->connection->close();
        }
        
        $this->logger->info('RabbitMQ connection closed');
    }

    public function publishToDeadLetter(array $data, string $reason): bool
    {
        $data['dead_letter_reason'] = $reason;
        $data['dead_letter_timestamp'] = time();
        
        return $this->publish('dead_letter_queue', $data);
    }

    /**
     * @throws Exception
     */
    public function setupDeadLetterQueue(): void
    {
        try {
            $this->connect();
            
            $this->channel->exchange_declare('dlx', 'direct', false, true, false);
            
            $this->channel->queue_declare('dead_letter_queue', false, true, false, false);
            
            $this->channel->queue_bind('dead_letter_queue', 'dlx', '');
            
            $this->logger->info('Dead letter queue setup completed');
        } catch (Exception $e) {
            $this->logger->error('Failed to setup dead letter queue', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}