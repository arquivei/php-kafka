<?php

namespace Kafka\Consumer;

use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\TopicConf;
use RdKafka\KafkaConsumer;
use Kafka\Consumer\Log\Logger;
use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Exceptions\KafkaConsumerException;

class Consumer
{
    private Config $config;
    private Logger $logger;
    private int $commits;
    private KafkaConsumer $consumer;
    private Producer $producer;
    private int $messageNumber = 0;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->logger = new Logger();
    }

    public function consume(): void
    {
        $this->consumer = new KafkaConsumer($this->setConf());
        $this->producer = new Producer($this->setConf());
        $this->consumer->subscribe($this->config->getTopics());

        $this->commits = 0;
        do {
            $message = $this->consumer->consume(120000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->messageNumber++;
                    $this->executeMessage($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // NO MESSAGE
                    break;
                default:
                    // ERROR
                    $this->logger->error($message, null, 'CONSUMER');
                    throw new KafkaConsumerException($message->errstr());
            }
        } while (!$this->isMaxMessage());
    }

    private function setConf(): Conf
    {
        $topicConf = new TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');

        $conf = new Conf();
        $conf->set('queued.max.messages.kbytes', '10000');
        $conf->set('enable.auto.commit', 'false');
        $conf->set('compression.codec', 'gzip');
        $conf->set('max.poll.interval.ms', '86400000');
        $conf->set('group.id', $this->config->getGroupId());
        $conf->set('bootstrap.servers', $this->config->getBroker());
        $conf->set('security.protocol', $this->config->getSecurityProtocol());
        $conf->setDefaultTopicConf($topicConf);

        if ($this->config->isPlainText()) {
            $conf->set('sasl.username', $this->config->getSasl()->getUsername());
            $conf->set('sasl.password', $this->config->getSasl()->getPassword());
            $conf->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
        }

        return $conf;
    }

    private function executeMessage(Message $message): void
    {
        try {
            $this->config->getConsumer()->handle($message->payload);
            $success = true;
        } catch (\Throwable $throwable) {
            $this->logger->error($message, $throwable);
            $success = $this->handleException($throwable, $message);
        }

        $this->commit($message, $success);
    }

    private function handleException(
        \Throwable $exception,
        Message $message
    ): bool {
        try {
            $this->config->getConsumer()->failed(
                $message->payload,
                $this->config->getTopics()[0],
                $exception
            );
            return true;
        } catch (\Throwable $throwable) {
            if ($exception !== $throwable) {
                $this->logger->error($message, $throwable, 'HANDLER_EXCEPTION');
            }
            return false;
        }
    }

    private function sendToDql(Message $message): void
    {
        $topic = $this->producer->newTopic($this->config->getDlq());
        $topic->produce(
            RD_KAFKA_PARTITION_UA,
            0,
            $message->payload,
            $this->config->getConsumer()->producerKey($message->payload)
        );
    }

    private function commit(Message $message, bool $success): void
    {
        try {
            if (!$success && !is_null($this->config->getDlq())) {
                $this->sendToDql($message);
                $this->commits = 0;
                $this->consumer->commit();
                return;
            }

            $this->commits++;
            if ($this->isMaxMessage() || $this->commits >= $this->config->getCommit()) {
                $this->commits = 0;
                $this->consumer->commit();
                return;
            }
        } catch (\Throwable $throwable) {
            $this->logger->error($message, $throwable, 'MESSAGE_COMMIT');
            if ($throwable->getCode() != RD_KAFKA_RESP_ERR__NO_OFFSET){
                throw $throwable;
            }
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageNumber == $this->config->getMaxMessages();
    }
}
