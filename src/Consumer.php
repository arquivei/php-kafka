<?php

namespace PHP\Kafka;

use PHP\Kafka\Exceptions\DontCommitException;
use Throwable;
use RdKafka\Message;
use RdKafka\KafkaConsumer;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\FailHandler\FailHandler;
use PHP\Kafka\FailHandler\CommitAlwaysFailHandler;
use PHP\Kafka\Exceptions\KafkaConsumerException;

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
        $this->kafka->subscribe($this->config->getConsumerConfig()->getTopics());
        do {
            $message = $this->kafka->consume($this->config->getConsumerConfig()->getTimeoutMs());
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
                    // ERROR
                    $this->logger->error('Unmapped Error while consuming from Kafka',
                        $this->messageToArray($message)
                    );
                    throw new KafkaConsumerException($message->errstr());
            }
        } while (!$this->isMaxMessage());
    }

    private function handle(Message $message): void
    {
        try {
            $this->config->getConsumerConfig()->getConsumer()->handle($message);
            $this->commit($message);
        } catch (Throwable $throwable) {
            $this->logger->error('Error while handling message',
                [
                    'consumer' => get_class($this->config->getConsumerConfig()->getConsumer()),
                    'message' => $this->messageToArray($message),
                    'exception' => $throwable,
                ]
            );
            $this->handleException($throwable, $message);
        }
    }

    private function handleException(Throwable $cause, Message $message): void
    {
        try {
            $this->config->getConsumerConfig()->getConsumer()->failed($message, $this->failHandler, $cause);
            $this->commit($message);
        } catch (Throwable $exception) {
            $this->logger->error('Error while handling exception',
                [
                    'consumer' => get_class($this->config->getConsumerConfig()->getConsumer()),
                    'failHandler' => get_class($this->failHandler),
                    'exception' => $exception,
                ]
            );
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
            $this->logger->error("Error while commit message",
                [
                    'exception' => $throwable,
                    'message' => $message,
                ]
            );
            if ($throwable->getCode() != RD_KAFKA_RESP_ERR__NO_OFFSET) {
                throw $throwable;
            }
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageNumber == $this->config->getConsumerConfig()->getMaxMessages();
    }

    /**
     * @param Message $message
     * @return array
     */
    private function messageToArray(Message $message): array
    {
        return [
            'message.err' => $message->err,
            'message.payload' => $message->payload,
            'message.topic_name' => $message->topic_name,
            'message.key' => $message->key,
            'message.headers' => $message->headers,
            'message.len' => $message->len,
            'message.offset' => $message->offset,
            'message.partition' => $message->partition,
            'message.timestamp' => $message->timestamp,
            'message.error' => $message->errstr(),
        ];
    }
}
