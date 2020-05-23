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

    public function __construct(
        array $topics,
        int $commit,
        string $groupId,
        Consumer $consumer,
        int $maxMessages = -1,
        int $timeoutMs = 120000,
        array $topicOptions = []
    ) {
        $this->topics = $topics;
        $this->commit = $commit;
        $this->groupId = $groupId;
        $this->consumer = $consumer;
        $this->maxMessages = $maxMessages;
        $this->timeoutMs = $timeoutMs;
        $this->topicOptions = $topicOptions;
    }

    /**
     * @return array
     */
    public function getTopics(): array
    {
        return $this->topics;
    }

    /**
     * @return int
     */
    public function getCommit(): int
    {
        return $this->commit;
    }

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @return Consumer
     */
    public function getConsumer(): Consumer
    {
        return $this->consumer;
    }

    /**
     * @return int
     */
    public function getMaxMessages(): int
    {
        return $this->maxMessages;
    }

    /**
     * @return int
     */
    public function getTimeoutMs(): int
    {
        return $this->timeoutMs;
    }

    /**
     * @return array
     */
    public function getTopicOptions(): array
    {
        return $this->topicOptions;
    }
}
