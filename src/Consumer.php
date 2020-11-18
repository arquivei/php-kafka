<?php

namespace PHP\Kafka;

use PHP\Kafka\Commit\CommitterBuilder;
use PHP\Kafka\Commit\NativeSleeper;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\CannotDecodeMessageException;
use PHP\Kafka\Exceptions\NoConsumerConfigurationException;
use Throwable;
use RdKafka\Message;
use RdKafka\KafkaConsumer;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\FailHandler\FailHandler;
use PHP\Kafka\Exceptions\KafkaConsumerException;
use PHP\Kafka\FailHandler\CommitAlwaysFailHandler;

class Consumer
{
    private const IGNORABLE_CONSUME_ERRORS = [
        RD_KAFKA_RESP_ERR__PARTITION_EOF,
        RD_KAFKA_RESP_ERR__TIMED_OUT,
        RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT,
        RD_KAFKA_RESP_ERR__TRANSPORT,
    ];

    private const IGNORABLE_COMMIT_ERRORS = [
        RD_KAFKA_RESP_ERR__NO_OFFSET,
    ];

    private Configuration $config;
    private LoggerInterface $logger;
    private FailHandler $failHandler;
    private KafkaConsumer $kafka;
    private MessageCounter $messageCounter;
    private Commit\Committer $committer;

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
        $this->messageCounter = new MessageCounter($this->getConsumerConfig()->getMaxMessages());
    }

    public function consume(): void
    {
        $consumerConfiguration = $this->getConsumerConfig();

        $this->committer = CommitterBuilder::withConsumer($this->kafka)
            ->andRetry(new NativeSleeper(), $consumerConfiguration->getMaxCommitRetries())
            ->committingInBatches($this->messageCounter, $consumerConfiguration->getCommit())
            ->build();

        $this->kafka->subscribe($consumerConfiguration->getTopics());
        do {
            $message = $this->kafka->consume((string) $consumerConfiguration->getTimeoutMs());
            $this->handleMessage($message);
        } while (!$this->isMaxMessage());
    }

    private function handleException(Throwable $cause, Message $message): void
    {
        try {
            $this->failHandler->handle($cause, $message);
            $this->committer->commitFailure();
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageCounter->isMaxMessage();
    }

    private function handleMessage(Message $message): void
    {
        if (RD_KAFKA_RESP_ERR_NO_ERROR === $message->err) {
            $this->messageCounter->add();
            $this->executeMessage($message);
            return;
        }

        if (!in_array($message->err, self::IGNORABLE_CONSUME_ERRORS)) {
            throw new KafkaConsumerException($message->errstr(), $message->err);
        }
    }

    private function executeMessage(Message $message): void
    {
        try {
            $decodedMessage = $this->decodeMessage($message->payload);
            $this->getConsumerConfig()->getHandler()($decodedMessage);
            $this->commit();
        } catch (Throwable $throwable) {
            $this->handleException($throwable, $message);
        }
    }

    private function commit(): void
    {
        try {
            $this->committer->commitMessage();
        } catch (Throwable $throwable) {
            if (!in_array($throwable->getCode(), self::IGNORABLE_COMMIT_ERRORS)) {
                throw $throwable;
            }
        }
    }

    private function getConsumerConfig(): ConsumerConfiguration
    {
        if ($config = $this->config->getConsumerConfig()) {
            return $config;
        }

        throw new NoConsumerConfigurationException();
    }

    /**
     * @param string $rawMessage
     *
     * @return mixed The decoded message
     */
    private function decodeMessage(string $rawMessage)
    {
        try {
            return $this->getConsumerConfig()->getDecoder()->decode($rawMessage);
        } catch (CannotDecodeMessageException $exception) {
            $this->getConsumerConfig()->getListener()->messageDecodingFailed($exception);
            return $rawMessage;
        }
    }
}
