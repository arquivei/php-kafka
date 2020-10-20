<?php

use PHP\Kafka\Log\PhpKafkaLogger;

require '../../vendor/autoload.php';

$logger = (new PhpKafkaLogger('simple-topic-example'))->getLogger();
$topicOptions = [];

$consumer = \PHP\Kafka\ConsumerBuilder::create(['simple-topic-example'])
    ->withGroupId('simple-consumer')
    ->withHandler(fn (string $message) => var_dump($message))
    ->withLogger($logger)
    ->build();

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}
