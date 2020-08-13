<?php

use PHP\Kafka\Producer;
use PHP\Kafka\Log\PhpKafkaLogger;
use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ProducerConfiguration;

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