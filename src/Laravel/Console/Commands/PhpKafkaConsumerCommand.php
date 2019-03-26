<?php

namespace Kafka\Consumer\Laravel\Console\Commands;

use Illuminate\Console\Command;

class PhpKafkaConsumerCommand extends Command
{
    protected $signature = 'arquivei:php-kafka-consumer {--topic=} {--groupId=} {--onlyDefault=} {--maxAttempt=}';

    protected $description = 'A consumer of Kafka in PHP';

    private $topic;
    private $config;
    private $groupId;
    private $maxAttempt;
    private $onlyDefault;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('php-kafka-consumer');
    }

    public function handle()
    {
        $this->topic = $this->option('topic');
        $this->groupId = $this->option('groupId');
        $this->maxAttempt = (int)$this->option('maxAttempt');
        $this->onlyDefault = (bool)$this->option('onlyDefault');

        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                $this->config['sasl']['username'],
                $this->config['sasl']['password'],
                $this->config['sasl']['mechanisms']
            ),
            $this->getTopic(),
            $this->config['broker'],
            $this->getGroupId(),
            new \Kafka\Consumer\Entities\Config\Consumer(
                $this->config['consumers']['default'],
                $this->config['consumers']['customs'],
                $this->getOnlyDefault()
            ),
            new \Kafka\Consumer\Entities\Config\MaxAttempt($this->getMaxAttempt()),
            $this->config['securityProtocol']
        );

        (new \Kafka\Consumer\Consumer($config))->consume();
    }

    private function getTopic(): string
    {
        return (is_string($this->topic) && strlen($this->topic) > 1) ? $this->topic : $this->config['topic'];
    }

    private function getGroupId(): string
    {
        return (is_string($this->groupId) && strlen($this->groupId) > 1) ? $this->groupId : $this->config['groupId'];
    }

    private function getOnlyDefault(): bool
    {
        return is_bool($this->onlyDefault) ? $this->onlyDefault : false;
    }

    private function getMaxAttempt(): ?int
    {
        return (is_int($this->maxAttempt) && $this->maxAttempt >= 1) ? $this->maxAttempt : null;
    }
}

