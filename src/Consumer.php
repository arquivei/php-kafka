<?php

namespace Kafka\Consumer;

use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Exceptions\KafkaConsumerException;

class Consumer
{
    private $config;
    private $commits;
    private $consumer;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function consume(): void
    {
        $this->consumer = new \RdKafka\KafkaConsumer($this->setConf());
        $this->consumer->subscribe([$this->config->getTopic()]);

        $this->commits = 0;
        while (true) {
            $message = $this->consumer->consume(500);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $this->executeMessage($message);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    // NO MESSAGE
                    break;
                default:
                    // ERROR
                    throw new KafkaConsumerException($message->errstr());
                    break;
            }
        }
    }

    private function setConf(): \RdKafka\Conf
    {
        $topicConf = new \RdKafka\TopicConf();
        $topicConf->set('auto.offset.reset', 'smallest');

        $conf = new \RdKafka\Conf();
        $conf->set('group.id', $this->config->getGroupId());
        $conf->set('metadata.broker.list', $this->config->getBroker());
        $conf->set('enable.auto.commit', 'false');
        $conf->setDefaultTopicConf($topicConf);
        $conf->set('security.protocol', $this->config->getSecurityProtocol());

        if ($this->config->isPlainText()) {
            $conf->set('sasl.mechanisms', $this->config->getSasl()->getMechanisms());
            $conf->set('sasl.username', $this->config->getSasl()->getUsername());
            $conf->set('sasl.password', $this->config->getSasl()->getPassword());
        }

        return $conf;
    }

    private function commit(\RdKafka\Message $message): void
    {
        if (!$this->config->getCommit()->isCommitInBatch()){
            $this->consumer->commit($message);
            return;
        }

        if ($this->commits >= $this->config->getCommit()->getValue()){
            $this->consumer->commit();
            $this->commits = 0;
            return;
        }

        $this->commits++;
    }

    private function executeMessage(\RdKafka\Message $message): void
    {
        $attempts = 1;
        $success = false;
        do {
            try {
                $consumer = $this->config->getConsumer();
                (new $consumer($message->payload))->handle();
                $success = true;
                $this->commit($message);
            } catch (\Throwable $exception) {
                if (
                    $this->config->getMaxAttempts()->hasMaxAttempts() &&
                    $this->config->getMaxAttempts()->hasReachedMaxAttempts($attempts)
                ) {
                    $success = true;
                    $this->commit($message);
                }
                $attempts++;
            }
        } while (!$success);
    }
}
