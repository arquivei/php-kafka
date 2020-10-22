<?php

namespace PHP\Kafka\FailHandler;

use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\DLQFailHandlerException;
use PHP\Kafka\Exceptions\NoConsumerConfigurationException;
use PHP\Kafka\Producer;
use Throwable;
use RdKafka\Message;
use Psr\Log\LoggerInterface;
use PHP\Kafka\Config\Configuration;
use RdKafka\Producer as KafkaProducer;

class DLQFailHandler implements FailHandler
{
    private Producer $producer;

    public function __construct(Producer $producer)
    {
        $this->producer = $producer;
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
            throw new DLQFailHandlerException('Error while sending to DLQ', $exception);
        }
    }

    private function sendToDql(?Message $message): void
    {
        if (null === $message) {
            return;
        }


        $this->producer->produce($message->payload, $message->key);
    }
}
