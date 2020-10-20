<?php

require '../../vendor/autoload.php';

$logger = (new PHP\Kafka\Log\PhpKafkaLogger('dlq-consumer'))->getLogger();

$consumer = \PHP\Kafka\ConsumerBuilder::create(['simple-topic-example'])
    ->withGroupId('dlq-consumer')
    ->withHandler(function () {
        throw new Exception("oops! I cannot handle this message...");
    })
    ->withDLQFailHandler()
    ->withLogger($logger)
    ->build();

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}
