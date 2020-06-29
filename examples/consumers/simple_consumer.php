<?php

use RdKafka\Message;
use PHP\Kafka\Log\PhpKafkaLogger;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;

require '../../vendor/autoload.php';

class SimpleConsumer extends Consumer {

    public function handle(Message $message): void
    {
        var_dump($message); // success
    }
}

$logger = (new PhpKafkaLogger('simple-topic-example'))->getLogger();
$topicOptions = [];
$consumerConfiguration = new ConsumerConfiguration(
    ['simple-topic-example'],
    1,
    'simple-consumer',
    new SimpleConsumer(),
    -1,
    12000,
    $topicOptions);

$configuration = new Configuration('localhost:9092',
    null,
    null,
    $consumerConfiguration);

$consumer = new \PHP\Kafka\Consumer($configuration, $logger);
try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}