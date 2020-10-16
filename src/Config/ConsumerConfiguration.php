<?php

namespace PHP\Kafka\Config;

use PHP\Kafka\Contracts\Consumer;

class ConsumerConfiguration
{
    //TODO PHP DOC

    private array $topics;
    private int $commit;
    private string $groupId;
    private Consumer $consumer;
    private int $maxMessages;
    private int $timeoutMs;
    private array $topicOptions;
    private int $maxCommitRetries;

    public function __construct(
        array $topics,
        int $commit,
        string $groupId,
        Consumer $consumer,
        int $maxMessages = -1,
        int $timeoutMs = 120000,
        array $topicOptions = [],
        int $maxCommitRetries = 6
    ) {
        $this->topics = $topics;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->consumer = $consumer;
        $this->maxMessages = $maxMessages;
        $this->timeoutMs = $timeoutMs;
        $this->topicOptions = $topicOptions;
        $this->maxCommitRetries = $maxCommitRetries;
    }

    public function getTopics(): array
    {
        return $this->topics;
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

    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }

    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }

    public function getTopicOptions(): array
    {
        return $this->topicOptions;
    }

    public function getMaxCommitRetries(): int
    {
        return $this->maxCommitRetries;
    }
}
