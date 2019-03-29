<?php

namespace Kafka\Consumer\Entities;

use Kafka\Consumer\Entities\Config\Sasl;
use Kafka\Consumer\Entities\Config\MaxAttempt;

class Config
{
    private $sasl;
    private $topic;
    private $broker;
    private $commit;
    private $groupId;
    private $consumer;
    private $maxAttempts;
    private $securityProtocol;

    public function __construct(
        Sasl $sasl,
        string $topic,
        string $broker,
        int $commit,
        string $groupId,
        string $consumer,
        MaxAttempt $maxAttempts,
        string $securityProtocol
    ) {
        $this->sasl = $sasl;
        $this->topic = $topic;
        $this->broker = $broker;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->consumer = $consumer;
        $this->maxAttempts = $maxAttempts;
        $this->securityProtocol = $securityProtocol;
    }

    public function getSasl(): Sasl
    {
        return $this->sasl;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getBroker(): string
    {
        return $this->broker;
    }

    public function getCommit(): int
    {
        return $this->commit;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getConsumer(): string
    {
        return $this->consumer;
    }

    public function getMaxAttempts(): MaxAttempt
    {
        return $this->maxAttempts;
    }

    public function getSecurityProtocol(): string
    {
        return $this->securityProtocol;
    }

    public function isPlainText(): bool
    {
        return $this->securityProtocol == 'SASL_PLAINTEXT';
    }
}
