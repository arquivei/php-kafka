<?php

namespace PHP\Kafka;

use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Exceptions\NoProducerConfigurationException;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;

class Producer
{
    private Configuration $config;
    private LoggerInterface $logger;
    private KafkaProducer $producer;

    public function __construct(
        Configuration $config,
        ?LoggerInterface $logger = null,
        ?KafkaProducer $producer = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? (new Log\PhpKafkaLogger())->getLogger();
        $this->producer = $producer ?? new KafkaProducer($config->buildConfigs());
    }

    public function produce(?string $message, ?string $key = null): void
    {
        $topic = $this->producer->newTopic($this->getProducerConfig()->getTopic());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message,
            $key
        );
        $this->producer->poll(1000);
    }

    private function getProducerConfig(): ProducerConfiguration
    {
        if ($config = $this->config->getProducerConfig()) {
            return $config;
        }

        throw new NoProducerConfigurationException();
    }
}
