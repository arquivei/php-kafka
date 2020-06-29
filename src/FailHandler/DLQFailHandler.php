<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;
use PHP\Kafka\Exceptions\DLQFailHandlerException;

class DLQFailHandler implements FailHandler
{
    private LoggerInterface $logger;
    private Configuration $configs;
    private KafkaProducer $producer;

    /**
     * DLQFailHandler constructor.
     *
     * @param Configuration      $configs
     * @param KafkaProducer|null $producer
     */
    public function __construct(Configuration $configs, ?KafkaProducer $producer = null)
    {
        $this->configs = $configs;
        $this->producer = $producer ?? new KafkaProducer($configs->buildConfigs());
    }

    /**
     * @param  Throwable    $cause
     * @param  Message|null $message
     * @throws DLQFailHandlerException
     */
    public function handle(Throwable $cause, ?Message $message): void
    {
        try {
            $this->sendToDql($message);
        } catch (Throwable $exception) {
            $error = sprintf(
                '%s. Consumer:%s',
                'Error while sending to DLQ',
                get_class($this->configs->getConsumerConfig()->getConsumer())
            );

            throw new DLQFailHandlerException($error, $exception);
        }
    }

    private function sendToDql(Message $message): void
    {
        $topic = $this->producer->newTopic($this->buildDlqTopic());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message->payload,
            $message->key
        );
        $this->producer->poll(1000);
    }

    private function buildDlqTopic(): string
    {
        $producerConfiguration = $this->configs->getProducerConfig();
        if (!is_null($producerConfiguration) && !is_null($producerConfiguration->getTopic())) {
            return $producerConfiguration->getTopic();
        } else {
            $consumerTopic = $this->configs->getConsumerConfig()->getTopics()[0];
            return $consumerTopic . '-dlq';
        }
    }
}
