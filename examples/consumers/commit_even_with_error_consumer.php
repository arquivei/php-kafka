<?php

use RdKafka\Message;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;

require '../../vendor/autoload.php';

class FailConsumer extends Consumer {

    public function handle(Message $message): void
    {
        throw new Exception("oops! some error...");
    }
}

$topicOptions = [];
$logger = (new PHP\Kafka\Log\PhpKafkaLogger('commit-even-with-error'))->getLogger();
$consumerConfiguration = new ConsumerConfiguration(
    ['simple-topic-example'],
    'commit_even_with_error_consumer',
    new PoisonMessageConsumer(),
    1,
    -1,
    12000,
    $topicOptions);

$configuration = new Configuration('localhost:9092',
    null,
    null,
    $consumerConfiguration);

$consumer = new \PHP\Kafka\Consumer($configuration);

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}
