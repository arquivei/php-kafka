<?php

declare(strict_types=1);

namespace PHP\Kafka;

use Closure;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Config\Sasl;
use PHP\Kafka\Decoder\MessageDecoder;
use PHP\Kafka\Decoder\NoDecoding;
use PHP\Kafka\FailHandler\DLQFailHandler;
use PHP\Kafka\FailHandler\FailHandler;
use PHP\Kafka\Listener\ConsumerListener;
use PHP\Kafka\Listener\NullListener;
use Psr\Log\LoggerInterface;

class ConsumerBuilder
{
    private array $topics;
    private int $commit;
    private string $groupId;
    private Closure $handler;
    private int $maxMessages;
    private int $timeoutMs;
    private array $topicOptions;
    private int $maxCommitRetries;
    private string $broker;
    private MessageDecoder $decoder;
    private ConsumerListener $listener;
    private ?FailHandler $failHandler = null;
    private ?LoggerInterface $logger = null;
    private ?Sasl $saslConfig = null;

    private function __construct(array $topics)
    {
        $this->topics = $topics;
        $this->commit = 1;
        $this->groupId = 'php-kafka-' . uniqid();
        $this->handler = function () {
        };
        $this->maxMessages = -1;
        $this->timeoutMs = 120000;
        $this->topicOptions = [];
        $this->maxCommitRetries = 6;
        $this->broker = 'localhost:9092';
        $this->decoder = new NoDecoding();
        $this->listener = new NullListener();
    }

    public static function create(array $topics): ConsumerBuilder
    {
        return new ConsumerBuilder($topics);
    }

    public function withCommitBatchSize(int $size): self
    {
        $this->commit = $size;
        return $this;
    }

    public function withGroupId(string $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function withHandler(callable $handler): self
    {
        $this->handler = Closure::fromCallable($handler);
        return $this;
    }

    public function withMaxMessages(int $maxMessages): self
    {
        $this->maxMessages = $maxMessages;
        return $this;
    }

    public function withTimeoutMs(int $timeoutMs): self
    {
        $this->timeoutMs = $timeoutMs;
        return $this;
    }

    public function withTopicOptions(array $topicOptions): self
    {
        $this->topicOptions = $topicOptions;
        return $this;
    }

    public function withMaxCommitRetries(int $maxCommitRetries): self
    {
        $this->maxCommitRetries = $maxCommitRetries;
        return $this;
    }

    public function withBroker(string $broker): self
    {
        $this->broker = $broker;
        return $this;
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function withFailHandler(FailHandler $failHandler): self
    {
        $this->failHandler = $failHandler;
        return $this;
    }

    public function withDLQFailHandler(?string $dlqTopic = null): self
    {
        if (null === $dlqTopic) {
            $dlqTopic = $this->topics[0] . '-dlq';
        }

        $producer = new Producer(
            new Configuration(
                $this->broker,
                $this->saslConfig,
                new ProducerConfiguration($dlqTopic),
                null
            ),
            $this->logger
        );

        $this->failHandler = new DLQFailHandler($producer);

        return $this;
    }

    public function withSasl(Sasl $saslConfig): self
    {
        $this->saslConfig = $saslConfig;
        return $this;
    }

    public function withListener(ConsumerListener $listener): self
    {
        $this->listener = $listener;
        return $this;
    }

    public function withDecoder(MessageDecoder $decoder): self
    {
        $this->decoder = $decoder;
        return $this;
    }

    public function build(): Consumer
    {
        $consumerConfig = new Config\ConsumerConfiguration(
            $this->topics,
            $this->commit,
            $this->groupId,
            $this->handler,
            $this->maxMessages,
            $this->timeoutMs,
            $this->topicOptions,
            $this->maxCommitRetries
        );

        return new Consumer(
            new Config\Configuration(
                $this->broker,
                $this->saslConfig,
                null,
                $consumerConfig
            ),
            $this->logger,
            $this->failHandler
        );
    }
}
