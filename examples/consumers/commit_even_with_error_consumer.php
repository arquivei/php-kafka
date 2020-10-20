<?php

require '../../vendor/autoload.php';

$topicOptions = [];
$logger = (new PHP\Kafka\Log\PhpKafkaLogger('commit-even-with-error'))->getLogger();

$consumer = \PHP\Kafka\ConsumerBuilder::create(['simple-topic-example'])
    ->withGroupId('commit_even_with_error_consumer')
    ->withHandler(function () {
        throw new Exception("oops! some error...");
    })
    ->withBroker('localhost:9092')
    ->withLogger($logger)
    ->build();

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}
