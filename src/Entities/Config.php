<?php

namespace Kafka\Consumer\Entities;

use Kafka\Consumer\Contracts\Consumer;
use Kafka\Consumer\Entities\Config\Sasl;

class Config
{
    private $dlq;
    private $sasl;
    private $topics;
    private $broker;
    private $commit;
    private $groupId;
    private $consumer;
    private $maxMessages;
    private $securityProtocol;

    public function __construct(
        Sasl $sasl,
        array $topics,
        string $broker,
        int $commit,
        string $groupId,
        Consumer $consumer,
        string $securityProtocol,
        ?string $dlq,
        int $maxMessages = -1
    ) {
        $this->dlq = $dlq;
        $this->sasl = $sasl;
        $this->topics = $topics;
        $this->broker = $broker;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->consumer = $consumer;
        $this->maxMessages = $maxMessages;
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

    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }
}
