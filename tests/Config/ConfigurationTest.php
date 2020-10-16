<?php

namespace Tests\Config;

use PHP\Kafka\Config\Configuration;
use PHP\Kafka\Config\ConsumerConfiguration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    public function testCreateDefaultConsumerConfiguration(): void
    {
        $topicOptions = [];
        $consumerConfiguration = new ConsumerConfiguration(
            ['topic'],
            1,
            'my-group-id',
            new MyConsumer(),
            -1,
            120000,
            $topicOptions
        );

        $configuration = new Configuration(
            'broker:port',
            null,
            null,
            $consumerConfiguration
        );

        $conf = $configuration->buildConfigs();

        $configs = $conf->dump();

        $this->assertEquals('broker:port', $configs['metadata.broker.list']);
        $this->assertEquals('10000', $configs['queued.max.messages.kbytes']);
        $this->assertEquals('false', $configs['enable.auto.commit']);
        $this->assertEquals('gzip', $configs['compression.codec']);
        $this->assertEquals('86400000', $configs['max.poll.interval.ms']);
    }
}
