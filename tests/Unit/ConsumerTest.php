<?php

namespace Tests\Unit;

use Monolog\Logger;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Consumer;
use PHP\Kafka\Exceptions\KafkaConsumerException;
use PHP\Kafka\FailHandler\DLQFailHandler;
use PHPUnit\Framework\TestCase;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use Tests\Fixture\FakeHandler;

class ConsumerTest extends TestCase
{
    private $kafkaConsumerMock;

    protected function setUp(): void
    {
        $this->kafkaConsumerMock = $this->createMock(KafkaConsumer::class);
    }

    public function testConsumeMessageWithSuccessAndCommit(): void
    {
        $fakeHandler = new FakeHandler();

        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-2',
            $fakeHandler,
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

        $this->kafkaConsumerMock->expects($this->once())->method('commit');

        $consumer = new Consumer($configuration, new Logger('test-logging'), null, $this->kafkaConsumerMock);
        $consumer->consume();

        $this->assertEquals($message->payload, $fakeHandler->lastMessage());
    }

    public function testConsumeMessageWithError(): void
    {
        $this->expectException(KafkaConsumerException::class);

        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-3',
            new FakeHandler(),
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

        $this->kafkaConsumerMock->expects($this->never())->method('commit');

        $consumer = new Consumer($configuration, new Logger('test-logging'), null, $this->kafkaConsumerMock);
        $consumer->consume();
    }

    public function testConsumeMessageHandlingErrorWhenFail(): void
    {
        $logger = new Logger('test-logging');

        $consumerConfiguration = new ConsumerConfiguration(
            ['topic-test'],
            1,
            'consumer-group-id-4',
            \Closure::fromCallable([$this, 'handleWithException']),
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

        $handler = new Consumer($configuration, $logger, $failHandlerMock, $this->kafkaConsumerMock);
        $handler->consume();
    }

    private function handleWithException()
    {
        throw new \Exception('ERROR');
    }
}
