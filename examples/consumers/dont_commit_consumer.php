<?php

use PHP\Kafka\FailHandler\DontCommitFailHandler;

require '../../vendor/autoload.php';

$topicOptions = [];
$logger = (new PHP\Kafka\Log\PhpKafkaLogger('dont-commit-consumer'))->getLogger();

$consumer = \PHP\Kafka\ConsumerBuilder::create(['simple-topic-example'])
    ->withGroupId('dont_commit_consumer')
    ->withHandler(function () {
        throw new Exception("oops! some error...");
    })
    ->withLogger($logger)
    ->withFailHandler(new DontCommitFailHandler())
    ->build();

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('Error', ['exception' => $e]);
}
