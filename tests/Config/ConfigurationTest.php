<?php

namespace Tests\Config;

use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHP\Kafka\Contracts\Consumer;
use PHPUnit\Framework\TestCase;
use RdKafka\Message;

class ConfigurationTest extends TestCase
{
    public function testCreateDefaultConsumerConfiguration(): void
    {
        $myConsumer = new class extends Consumer {

            public function handle(Message $message): void
            {
            }
        };

        $topicOptions = [];
        $consumerConfiguration = new ConsumerConfiguration(
            ['topic'],
            'my-group-id',
            $myConsumer,
            1,
            -1,
            120000,
            $topicOptions);

        $configuration = new Configuration('broker:port',
            null,
            null,
            $consumerConfiguration);

        $conf = $configuration->buildConfigs();

        $configs = $conf->dump();

        $this->assertEquals('broker:port', $configs['metadata.broker.list']);
        $this->assertEquals('10000', $configs['queued.max.messages.kbytes']);
        $this->assertEquals('false', $configs['enable.auto.commit']);
        $this->assertEquals('gzip', $configs['compression.codec']);
        $this->assertEquals('86400000', $configs['max.poll.interval.ms']);
    }
}
