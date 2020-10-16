<?php

namespace Tests;

use Monolog\Logger;
use RdKafka\Message;
use PHP\Kafka\Consumer;
use RdKafka\KafkaConsumer;
use PHPUnit\Framework\TestCase;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\FailHandler\DLQFailHandler;
use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;

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
            'consumer-group-id-2',
            $this->consumerMock,
            1,
            1,
            12000,
            []
        );

        $configuration = new Configuration(
            'localhost:9092',
            null,
            null,
            $consumerConfiguration
        );

        $message = new Message();
        $message->err = 0;
        $message->key = 'key';
        $message->topic_name = 'topic-test';
        $message->payload = 'message payload';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($message);

        $this->consumerMock->expects($this->once())->method('handle')->with($this->equalTo($message));
        $this->kafkaConsumerMock->expects($this->once())->method('commit');

        $consumer = new Consumer($configuration, new Logger('test-logging'), null, $this->kafkaConsumerMock);
        $consumer->consume();
    }

    public function testConsumeMessageWithError(): void
    {
        $this->expectException(KafkaConsumerException::class);
        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            'consumer-group-id-3',
            $this->consumerMock,
            1,
            -1,
            12000,
            []
        );

        $configuration = new Configuration(
            'localhost:9092',
            null,
            null,
            $consumerConfiguration
        );

        $errorMessage = new Message();
        $errorMessage->err = 1;
        $errorMessage->topic_name = 'topic-test';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($errorMessage);

        $this->consumerMock->expects($this->never())->method('handle');
        $this->kafkaConsumerMock->expects($this->never())->method('commit');

        $consumer = new Consumer($configuration, new Logger('test-logging'), null, $this->kafkaConsumerMock);
        $consumer->consume();
    }

    public function testConsumeMessageHandlingErrorWhenFail(): void
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
            'consumer-group-id-4',
            $consumer,
            1,
            1,
            12000,
            []
        );

        $producerConfiguration = new ProducerConfiguration('topic-dlq', 'all');

        $configuration = new Configuration(
            'localhost:9092',
            null,
            $producerConfiguration,
            $consumerConfiguration
        );

        $message = new Message();
        $message->err = 0;
        $message->key = 'key';
        $message->topic_name = 'topic-test';
        $message->payload = 'message payload';

        $this->kafkaConsumerMock->method('subscribe')->withAnyParameters()->willReturnSelf();
        $this->kafkaConsumerMock->method('consume')->withAnyParameters()->willReturn($message);
        $failHandlerMock = $this->createMock(DLQFailHandler::class);

        $this->kafkaConsumerMock->expects($this->once())->method('commit');

        $consumer = new Consumer($configuration, $logger, $failHandlerMock, $this->kafkaConsumerMock);
        $consumer->consume();
    }
}
