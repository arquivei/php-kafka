<?php

declare(strict_types=1);

namespace PHP\Kafka\Listener;

class NullListener implements ConsumerListener
{
    public function messageDecodingFailed(string $rawMessage): void
    {
    }
}
