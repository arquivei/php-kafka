<?php

namespace PHP\Kafka\Config;

use RdKafka\Conf;

class Configuration
{
    private string $broker;
    private Conf $conf;
    private ?Sasl $sasl;
    private ?ProducerConfiguration $producerConf;
    private ?ConsumerConfiguration $consumerConfig;

    /**
     * Configuration constructor.
     * @param string $broker
     * @param Sasl $sasl
     * @param ProducerConfiguration $producerConf
     * @param ConsumerConfiguration $consumerConfig
     */
    public function __construct(
        string $broker,
        Sasl $sasl = null,
        ProducerConfiguration $producerConf = null,
        ConsumerConfiguration $consumerConfig = null
    ) {
        $this->broker = $broker;
        $this->sasl = $sasl;
        $this->producerConf = $producerConf;
        $this->consumerConfig = $consumerConfig;
        $this->conf = new Conf();
    }

    /**
     * @return string
     */
    public function getBroker(): string
    {
        return $this->broker;
    }

    /**
     * @return ConsumerConfiguration|null
     */
    public function getConsumerConfig(): ?ConsumerConfiguration
    {
        return $this->consumerConfig;
    }

    /**
     * @return ProducerConfiguration|null
     */
    public function getProducerConfig(): ?ProducerConfiguration
    {
        return $this->producerConf;
    }

    /**
     * @return Sasl|null
     */
    public function getSasl(): ?Sasl
    {
        return $this->sasl;
    }

    public function getConf(): Conf
    {
        return $this->conf;
    }

    public function buildConfigs(array $options = []): Conf
    {
        $options = array_merge($this->defaultGlobalConfigs(), $options);

        $this->buildGlobalConfigurations($options, $this->conf);
        $this->buildSaslConfig($this->conf);
        $this->buildConsumerConfig($this->conf);
        $this->buildProducerConfig($this->conf);

        return $this->conf;
    }

    private function buildSaslConfig(Conf $conf): void
    {
        if (!is_null($this->getSasl()) && $this->getSasl()->isPlainText()) {
            $conf->set('sasl.username', $this->getSasl()->getUsername());
            $conf->set('sasl.password', $this->getSasl()->getPassword());
            $conf->set('sasl.mechanisms', $this->getSasl()->getMechanisms());
            $conf->set('security.protocol', $this->getSasl()->getSecurityProtocol());
        }
    }

    private function buildConsumerConfig(Conf $conf): void
    {
        if (is_null($this->consumerConfig)) {
            $conf->set('group.id', 'php-kafka-consumer');
        } else {
            $conf->set('group.id', $this->getConsumerConfig()->getGroupId());
        }
    }
    private function buildProducerConfig(Conf $conf): void
    {
        $conf->set('enable.idempotence', 'true');
        if(is_null($this->producerConf)){
            $conf->set('acks', '-1');
        } else {
            $conf->set('acks', $this->getProducerConfig()->getAcks());
        }
    }

    private function buildGlobalConfigurations(?array $options, Conf $conf): void
    {
        $conf->set('bootstrap.servers', $this->getBroker());
        if (!is_null($options)) {
            foreach ($options as $key => $value) {
                $conf->set($key, $value);
            }
        }
    }

    private function defaultGlobalConfigs(): array
    {
        return [
            'queued.max.messages.kbytes' => '10000',
            'enable.auto.commit' => 'false',
            'compression.codec' =>'gzip',
            'max.poll.interval.ms' => '86400000',
            'auto.offset.reset' => 'smallest',
        ];
    }
}
