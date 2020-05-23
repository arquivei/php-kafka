<?php

use PHP\Kafka\FailHandler\DontCommitFailHandler;
use RdKafka\Message;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;

require '../../vendor/autoload.php';

class ThrowableConsumer extends Consumer {

    public function handle(Message $message): void
    {
        throw new Exception("oops! some error...");
    }
}

$topicOptions = [];
$logger = (new PHP\Kafka\Log\PhpKafkaLogger('dont-commit-consumer'))->getLogger();
$consumerConfiguration = new ConsumerConfiguration(
    ['simple-topic-example'],
    1,
    'dont_commit_consumer',
    new ThrowableConsumer(),
    -1,
    12000,
    $topicOptions);

$configuration = new Configuration('localhost:9092',
    null,
    null,
    $consumerConfiguration);

$consumer = new \PHP\Kafka\Consumer($configuration, null, new DontCommitFailHandler());

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('Error', ['exception' => $e]);
}