<?php

namespace PHP\Kafka;

use Throwable;
use RdKafka\Message;
use RdKafka\KafkaConsumer;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\FailHandler\FailHandler;
use PHP\Kafka\Exceptions\KafkaConsumerException;
use PHP\Kafka\Exceptions\InvalidConsumerException;
use PHP\Kafka\FailHandler\CommitAlwaysFailHandler;

class Consumer
{
    private int $commits;
    private int $messageNumber = 0;
    private Configuration $config;
    private LoggerInterface $logger;
    private FailHandler $failHandler;
    private KafkaConsumer $kafka;

    public function __construct(
        Configuration $config,
        ?LoggerInterface $logger = null,
        ?FailHandler $failHandler = null,
        ?KafkaConsumer $kafka = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? (new Log\PhpKafkaLogger())->getLogger();
        $this->failHandler = $failHandler ?? new CommitAlwaysFailHandler();
        $this->kafka = $kafka ?? new KafkaConsumer($this->config->buildConfigs());
    }

    public function consume(): void
    {
        $this->commits = 0;
        $consumerConfiguration = $this->config->getConsumerConfig();
        if(is_null($consumerConfiguration)) {
            throw new InvalidConsumerException();
        }

        $this->kafka->subscribe($consumerConfiguration->getTopics());
        do {
            $message = $this->kafka->consume($consumerConfiguration->getTimeoutMs());
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->messageNumber++;
                    $this->handle($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // NO MESSAGE
                    break;
                default:
                    throw new KafkaConsumerException($message->errstr());
            }
        } while (!$this->isMaxMessage());
    }

    private function handle(Message $message): void
    {
        try {
            $consumerConfiguration = $this->config->getConsumerConfig();
            if(is_null($consumerConfiguration)) {
               throw new InvalidConsumerException();
            }

            $consumer = $consumerConfiguration->getConsumer();
            $consumer->handle($message);
            $this->commit($message);
        } catch (Throwable $throwable) {
            $this->handleException($throwable, $message);
        }
    }

    private function handleException(Throwable $cause, Message $message): void
    {
        try {
            $this->config->getConsumerConfig()->getConsumer()->failed($message, $this->failHandler, $cause);
            $this->commit($message);
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    private function commit(Message $message): void
    {
        try {
            $this->commits++;
            if ($this->isMaxMessage() || $this->commits >= $this->config->getConsumerConfig()->getCommit()) {
                $this->commits = 0;
                $this->kafka->commit();
                return;
            }
        } catch (Throwable $throwable) {
            if ($throwable->getCode() != RD_KAFKA_RESP_ERR__NO_OFFSET) {
                throw $throwable;
            }
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageNumber == $this->config->getConsumerConfig()->getMaxMessages();
    }
}
