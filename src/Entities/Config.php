<?php

namespace Kafka\Consumer\Entities;

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config\Sasl;
use Kafka\Consumer\Entities\Config\MaxAttempt;
use Kafka\Consumer\Entities\Config\Sleep;

class Config
{
    private $dlq;
    private $sasl;
    private $topics;
    private $sleep;
    private $broker;
    private $commit;
    private $groupId;
    private $consumer;
    private $maxAttempts;
    private $securityProtocol;

    public function __construct(
        Sasl $sasl,
        array $topics,
        string $broker,
        int $commit,
        string $groupId,
        Consumer $consumer,
        MaxAttempt $maxAttempts,
        string $securityProtocol,
        ?string $dlq,
        Sleep $sleep
    ) {
        $this->dlq = $dlq;
        $this->sasl = $sasl;
        $this->topics = $topics;
        $this->sleep = $sleep;
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

    public function getTopics(): array
    {
        return $this->topics;
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

    public function getConsumer(): Consumer
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

    public function getDlq(): ?string
    {
        return $this->dlq;
    }

    public function getSleep(): Sleep
    {
        return $this->sleep;
    }
}
