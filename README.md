# PHP Kafka

[![Build Status](https://github.com/arquivei/php-kafka/workflows/Build/badge.svg)](https://github.com/arquivei/php-kafka/actions)

A PHP client for Apache Kafka

## Requirements

+ PHP 7.4
+ librdkafka 1.1.0
+ rdkafka 3.x


## Install

1. Install [librdkafka c library](https://github.com/edenhill/librdkafka)

    ```bash
    $ cd /tmp
    $ mkdir librdkafka
    $ cd librdkafka
    $ curl -L https://github.com/edenhill/librdkafka/archive/v1.0.0.tar.gz | tar xz
    $ cd librdkafka-1.0.0
    $ ./configure
    $ make
    $ make install
    ```

2. Install the [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) PECL extension

    ```bash
    $ pecl install rdkafka
    ```

3. Add the following to your php.ini file to enable the php-rdkafka extension

    `extension=rdkafka.so`

4. Install this package via composer using:

    `composer require arquivei/php-kafka-consumer`
    
## Usage 

```php
<?php

require_once 'vendor/autoload.php';

use Kafka\Consumer\Entities\Config;
use Kafka\Consumer\Contracts\ConsumerDLQ;
use Kafka\Consumer\Entities\Config\Sasl;
use Kafka\Consumer\Entities\Config\MaxAttempt;

class DefaultConsumer extends Consumer
{
    public function handle(string $message): void
    {
        print 'Init: ' . date('Y-m-d H:i:s') . PHP_EOL;
        sleep(2);
        print 'Finish: ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}

$config = new Config(
    new Sasl('username', 'pasword', 'mechanisms'),
    'topic',
    'broker:port',
    1,
    'php-kafka-consumer-group-id',
    new DefaultConsumer(),
    'security-protocol'
);

(new \PHP\Kafka\Consumer($config))->consume();



$config = new Config(
    new Sasl('username', 'pasword', 'mechanisms'),
    'topic',
    'broker:port',
    1,
    'php-kafka-consumer-group-id',
    new DLDConsumer(),
    new DLQ("parking-lot"),
    'security-protocol'
);

(new \PHP\Kafka\Consumer($config))->consume();

```
