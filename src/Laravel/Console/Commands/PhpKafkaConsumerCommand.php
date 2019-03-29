<?php

namespace Kafka\Consumer\Laravel\Console\Commands;

use Illuminate\Console\Command;
use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config\Commit;
use Kafka\Consumer\Exceptions\InvalidConsumerException;

class PhpKafkaConsumerCommand extends Command
{
    protected $signature = 'arquivei:php-kafka-consumer {--topic=} {--consumer=} {--groupId=} {--maxAttempt=} {--commitBatch=}';

    protected $description = 'A consumer of Kafka in PHP';

    private $topic;
    private $config;
    private $groupId;
    private $maxAttempt;
    private $commitBatch;
    private $onlyDefault;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('php-kafka-consumer');
    }

    public function handle()
    {
        $consumer = $this->option('consumer');
        $this->validateConsumer($consumer);

        $this->topic = $this->option('topic');
        $this->groupId = $this->option('groupId');
        $this->maxAttempt = (int)$this->option('maxAttempt');
        $this->commitBatch = (int)$this->option('commitBatch');


        $config = new \Kafka\Consumer\Entities\Config(
            new \Kafka\Consumer\Entities\Config\Sasl(
                $this->config['sasl']['username'],
                $this->config['sasl']['password'],
                $this->config['sasl']['mechanisms']
            ),
            $this->getTopic(),
            $this->config['broker'],
            new Commit($this->getCommitBatch()),
            $this->getGroupId(),
            $consumer,
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

    private function getMaxAttempt(): ?int
    {
        return (is_int($this->maxAttempt) && $this->maxAttempt >= 1) ? $this->maxAttempt : null;
    }

    private function getCommitBatch(): ?int
    {
        return (is_int($this->commitBatch) && $this->commitBatch > 1) ? $this->commitBatch : null;
    }

    private function validateConsumer(?string $consumer): void
    {
        if (!class_exists($consumer) || !in_array(Consumer::class, class_implements($consumer))){
            throw new InvalidConsumerException();
        }
    }
}

