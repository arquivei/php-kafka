<?php

use PHP\Kafka\FailHandler\DLQFailHandler;
use PHP\Kafka\FailHandler\DontCommitFailHandler;
use RdKafka\Message;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;

require '../../vendor/autoload.php';

class PoisonMessageConsumer extends Consumer {

    public function handle(Message $message): void
    {
        throw new Exception("oops! I cannot handle this message...");
    }
}

$topicOptions = [];
$logger = (new PHP\Kafka\Log\PhpKafkaLogger('dlq-consumer'))->getLogger();

$consumerConfiguration = new ConsumerConfiguration(
    ['simple-topic-example'],
    1,
    'dlq-consumer',
    new PoisonMessageConsumer(),
    -1,
    12000,
    $topicOptions);

$configuration = new Configuration('localhost:9092',
    null,
    null,
    $consumerConfiguration);

$dlqFailHandler = new DLQFailHandler($configuration, $logger);

$consumer = new \PHP\Kafka\Consumer($configuration, $logger, $dlqFailHandler);

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}