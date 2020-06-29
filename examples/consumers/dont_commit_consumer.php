<?php

use RdKafka\Message;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\FailHandler\DontCommitFailHandler;

require '../../vendor/autoload.php';

class ThrowableConsumer extends Consumer {

    public function handle(Message $message): void
    {
        throw new Exception("oops! some error...");
    }
}

$logger = (new PHP\Kafka\Log\PhpKafkaLogger('dont-commit-consumer'))->getLogger();
$consumerConfiguration = new ConsumerConfiguration(
    ['simple-topic-example'],
    'dont_commit_consumer',
    new ThrowableConsumer());

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
