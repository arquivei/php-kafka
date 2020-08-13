<?php

namespace PHP\Kafka;

use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;

class Producer
{
    private Configuration $config;
    private ?LoggerInterface $logger;
    private ?KafkaProducer $producer;

    public function __construct(
        Configuration $config,
        ?LoggerInterface $logger = null,
        ?KafkaProducer $producer = null
    ) {
        $this->config = $config;
        $this->logger = $logger ?? (new Log\PhpKafkaLogger())->getLogger();
        $this->producer = $producer ?? new KafkaProducer($config->buildConfigs());
    }

    public function produce($message, ?string $key = null): void
    {
        $topic = $this->producer->newTopic($this->config->getProducerConfig()->getTopic());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message,
            $key
        );
        $this->producer->poll(1000);
    }
}
