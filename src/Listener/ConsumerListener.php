<?php

declare(strict_types=1);

namespace PHP\Kafka\Listener;

interface ConsumerListener
{
    /**
     * Called when a message cannot be decoded
     *
     * @param string $rawMessage The message to be decoded
     */
    public function messageDecodingFailed(string $rawMessage): void;
}
