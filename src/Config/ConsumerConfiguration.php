<?php

namespace PHP\Kafka\Config;

use Closure;
use PHP\Kafka\Decoder\MessageDecoder;
use PHP\Kafka\Decoder\NoDecoding;
use PHP\Kafka\Listener\ConsumerListener;
use PHP\Kafka\Listener\NullListener;

class ConsumerConfiguration
{
    private array $topics;
    private int $commit;
    private string $groupId;
    private Closure $handler;
    private int $maxMessages;
    private int $timeoutMs;
    private array $topicOptions;
    private int $maxCommitRetries;
    private ConsumerListener $listener;
    private MessageDecoder $decoder;

    public function __construct(
        array $topics,
        int $commit,
        string $groupId,
        callable $handler,
        int $maxMessages = -1,
        int $timeoutMs = 120000,
        array $topicOptions = [],
        int $maxCommitRetries = 6,
        ?ConsumerListener $consumerListener = null,
        ?MessageDecoder $messageDecoder = null
    ) {
        $this->topics = $topics;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->handler = Closure::fromCallable($handler);
        $this->maxMessages = $maxMessages;
        $this->timeoutMs = $timeoutMs;
        $this->topicOptions = $topicOptions;
        $this->maxCommitRetries = $maxCommitRetries;
        $this->listener = $consumerListener ?? new NullListener();
        $this->decoder = $messageDecoder ?? new NoDecoding();
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

    public function getHandler(): callable
    {
        return $this->handler;
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

    public function getListener(): ConsumerListener
    {
        return $this->listener;
    }

    public function getDecoder(): MessageDecoder
    {
        return $this->decoder;
    }
}
