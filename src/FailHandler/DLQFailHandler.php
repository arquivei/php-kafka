<?php

namespace PHP\Kafka\FailHandler;

use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\DLQFailHandlerException;
use PHP\Kafka\Exceptions\NoConsumerConfigurationException;
use Throwable;
use RdKafka\Message;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;

class DLQFailHandler implements FailHandler
{
    private LoggerInterface $logger;
    private Configuration $config;
    private KafkaProducer $producer;

    /**
     * DLQFailHandler constructor.
     *
     * @param Configuration      $configs
     * @param KafkaProducer|null $producer
     */
    public function __construct(Configuration $configs, ?KafkaProducer $producer = null)
    {
        $this->config = $configs;
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
                get_class($this->getConsumerConfig()->getConsumer())
            );

            throw new DLQFailHandlerException($error, $exception);
        }
    }

    private function sendToDql(?Message $message): void
    {
        $topic = $this->producer->newTopic($this->buildDlqTopic());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            is_null($message) ? null : $message->payload,
            is_null($message) ? null : $message->key
        );
        $this->producer->poll(1000);
    }

    private function buildDlqTopic(): string
    {
        $producerConfiguration = $this->config->getProducerConfig();
        if (!is_null($producerConfiguration)) {
            return $producerConfiguration->getTopic();
        } else {
            $consumerTopic = $this->getConsumerConfig()->getTopics()[0];
            return $consumerTopic . '-dlq';
        }
    }

    private function getConsumerConfig(): ConsumerConfiguration
    {
        if ($config = $this->config->getConsumerConfig()) {
            return $config;
        }

        throw new NoConsumerConfigurationException();
    }
}
