<?php

use PHP\Kafka\Log\PhpKafkaLogger;

require '../../vendor/autoload.php';

class LoggingListener implements \PHP\Kafka\Listener\ConsumerListener
{
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function messageDecodingFailed(string $rawMessage): void
    {
        $this->logger->warning('Received a message that could not be decoded', [
            'message' => $rawMessage,
        ]);
    }
}

$logger = (new PhpKafkaLogger('simple-topic-example'))->getLogger();
$topicOptions = [];

$consumer = \PHP\Kafka\ConsumerBuilder::create(['simple-topic-example'])
    ->withGroupId('simple-consumer')
    ->withHandler(fn (string $message) => var_dump($message))
    ->withLogger($logger)
    ->withDecoder(new \PHP\Kafka\Decoder\JsonDecoder())
    ->withListener(new LoggingListener($logger))
    ->build();

try {
    $consumer->consume();
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}
