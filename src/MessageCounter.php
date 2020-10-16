<?php

declare(strict_types=1);

namespace PHP\Kafka;

class MessageCounter
{
    private int $messageCount = 0;
    private int $maxMessages;

    public function __construct(int $maxMessages)
    {
        $this->maxMessages = $maxMessages;
    }

    public function add(): void
    {
        $this->messageCount++;
    }

    public function isMaxMessage(): bool
    {
        return $this->messageCount === $this->maxMessages;
    }
}
