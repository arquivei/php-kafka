<?php

namespace Tests\FailHandler;

use Exception;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use PHPUnit\Framework\TestCase;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\FailHandler\DLQFailHandler;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Exceptions\DLQFailHandlerException;

class DLQFailHandlerTest extends TestCase
{

    public function testHandlingWithDefaultConfiguration()
    {
        $consumer = new class extends Consumer {

            public function handle(Message $message): void
            {
            }
        };
        $producerMock = $this->createMock(Producer::class);
        $producerTopicMock = $this->createMock(ProducerTopic::class);
        $producerMock->method('newTopic')->withAnyParameters(self::anything())->willReturn($producerTopicMock);

        $consumerConfiguration = new ConsumerConfiguration(['topic'], 'group', $consumer);
        $configuration = new Configuration('broker', null, null, $consumerConfiguration);

        $failHandler = new DLQFailHandler($configuration, $producerMock);
        $message = new Message();
        $message->payload = 'payload';
        $message->key = 'key-123';

        $producerTopicMock->expects($this->once())->method('produce')->with(-1,
            0,
            $message->payload,
            $message->key);

        $failHandler->handle(new \Exception('previous error'), $message);
    }

    public function testHandlingWithCustomProducerConfiguration()
    {
        $consumer = new class extends Consumer {

            public function handle(Message $message): void
            {
            }
        };
        $producerMock = $this->createMock(Producer::class);
        $producerTopicMock = $this->createMock(ProducerTopic::class);
        $producerMock->method('newTopic')->withAnyParameters(self::anything())->willReturn($producerTopicMock);

        $consumerConfiguration = new ConsumerConfiguration(['topic'], 'group', $consumer);
        $producerConfiguration = new ProducerConfiguration('my-dlq-topic');
        $configuration = new Configuration('broker', null, $producerConfiguration, $consumerConfiguration);

        $failHandler = new DLQFailHandler($configuration, $producerMock);
        $message = new Message();
        $message->payload = 'payload';
        $message->key = 'key-123';

        $producerTopicMock->expects($this->once())->method('produce')->with(-1,
            0,
            $message->payload,
            $message->key);

        $failHandler->handle(new \Exception('previous error'), $message);
    }

    public function testHandlingThrowException()
    {
        $this->expectException(DLQFailHandlerException::class);
        $consumer = new class extends Consumer {

            public function handle(Message $message): void
            {
            }
        };

        $producerMock = $this->createMock(Producer::class);
        $producerMock->method('newTopic')->willThrowException(new Exception('Oops!'));

        $consumerConfiguration = new ConsumerConfiguration(['topic'], 'group', $consumer);
        $configuration = new Configuration('broker', null, null, $consumerConfiguration);

        $failHandler = new DLQFailHandler($configuration, $producerMock);
        $message = new Message();
        $message->payload = 'payload';
        $message->key = 'key-123';

        $failHandler->handle(new \Exception('previous error'), $message);
    }
}
