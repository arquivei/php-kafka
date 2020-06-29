<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;

class DLQFailHandler implements FailHandler
{
    private LoggerInterface $logger;
    private Configuration $configs;
    private KafkaProducer $producer;

    /**
     * DLQFailHandler constructor.
     * @param LoggerInterface $logger
     * @param Configuration $configs
     */
    public function __construct(Configuration $configs, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->configs = $configs;
        $this->producer = new KafkaProducer($configs->buildConfigs());
    }

    public function handle(Throwable $cause, ?Message $message): void
    {
        try {
            $this->sendToDql($message);
        } catch (Throwable $exception) {
            $this->logger->error('Error while sending to DLQ',
                [
                    'consumer' => get_class($this->configs->getConsumerConfig()->getConsumer()),
                    'exception' => $exception,
                ]
            );
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
        $this->logger->info('Message Sent to DLQ',
            [
                'message' => $message->payload,
            ]
        );
    }

    /*private function buildConfs(): TopicConf
    {
        $topicConf = new TopicConf();
        $producerConfiguration = $this->configs->getProducerConfig();
        $dump = $this->configs->getConf()->dump();

        foreach ($dump as $key => $value) {
            $topicConf->set($key, $value);
        }
        $topicConf->set('enable.idempotence', 'true');
        $topicConf->set('acks', $producerConfiguration->getAcks());

        return $topicConf;
    }*/

    /**
     * @return string
     */
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