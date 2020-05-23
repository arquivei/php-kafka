<?php

use PHP\Kafka\Config\ProducerConfiguration;
use PHP\Kafka\Producer;
use RdKafka\Message;
use PHP\Kafka\Log\PhpKafkaLogger;
use PHP\Kafka\Contracts\Consumer;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Exceptions\KafkaConsumerException;

require '../../vendor/autoload.php';

$logger = (new PhpKafkaLogger('simple-producer-example'))->getLogger();
$topicOptions = [];
$producerConfiguration = new ProducerConfiguration('cool-topic');
$configuration = new Configuration('localhost:9092',
    null,
    $producerConfiguration,
    null);

$producer = new Producer($configuration, $logger);
try {
    $producer->produce('cool message','key-123');
} catch (Throwable $e) {
    $logger->error('error', ['exception' => $e]);
}