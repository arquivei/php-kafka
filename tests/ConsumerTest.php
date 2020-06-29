<?php

namespace Tests;

use Monolog\Logger;
use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\FailHandler\DLQFailHandler;
use RdKafka\Message;
use PHP\Kafka\Consumer;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;
use PHP\Kafka\Exceptions\InvalidConsumerException;

class ConsumerTest extends TestCase
{
    private $consumerMock;
    private $kafkaConsumerMock;

    protected function setUp(): void
    {
        $this->kafkaConsumerMock = $this->createMock(KafkaConsumer::class);
        $this->consumerMock = $this->getMockForAbstractClass(\PHP\Kafka\Contracts\Consumer::class);
    }

    public function testConsumeMessageWithSuccessAndCommit(): void
    {
        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-2',
            $this->consumerMock,
            1,
            12000,
            []);

        $configuration = new Configuration('localhost:9092',
            null,
            null,
            $consumerConfiguration);

        $message = new Message();
        $message->err = 0;
        $message->key = 'key';
        $message->topic_name = 'topic-test';
        $message->payload = 'message payload';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($message);

        $this->consumerMock->expects($this->once())->method('handle')->with($this->equalTo($message));

        $consumer = new Consumer($configuration, new Logger('test-logging'), $this->kafkaConsumerMock);
        $consumer->consume();
    }

    public function testConsumeMessageWithError(): void
    {
        $this->expectException(KafkaConsumerException::class);
        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-3',
            $this->consumerMock,
            -1,
            12000,
            []);

        $configuration = new Configuration('localhost:9092',
            null,
            null,
            $consumerConfiguration);

        $errorMessage = new Message();
        $errorMessage->err = 1;
        $errorMessage->topic_name = 'topic-test';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($errorMessage);

        $this->consumerMock->expects($this->never())->method('handle');

        $consumer = new Consumer($configuration, new Logger('test-logging'), $this->kafkaConsumerMock);
        $consumer->consume();
    }

    public function testConsumeMessageDLQFailHandling(): void
    {
        $logger = new Logger('test-logging');
        $consumer = new class extends \PHP\Kafka\Contracts\Consumer
        {
            public function handle(Message $message): void
            {
                throw new \Exception("ERROR");
            }
        };

        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-4',
            $consumer,
            1,
            12000,
            []);

        $producerConfiguration = new ProducerConfiguration('topic-dlq', 'all');

        $configuration = new Configuration('localhost:9092',
            null,
            $producerConfiguration,
            $consumerConfiguration);

        $message = new Message();
        $message->err = 0;
        $message->key = 'key';
        $message->topic_name = 'topic-test';
        $message->payload = 'message payload';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($message);


        $failHandler = new DLQFailHandler($logger, $configuration);

        $consumer = new Consumer($configuration, $logger, $this->kafkaConsumerMock, $failHandler);
        $consumer->consume();
    }
}
