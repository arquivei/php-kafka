<?php

namespace PHP\Kafka\Contracts;

use Throwable;
use RdKafka\Message;
use PHP\Kafka\FailHandler\FailHandler;

abstract class Consumer
{
    abstract public function handle(Message $message): void;

    public function failed(Message $message, FailHandler $failHandler, Throwable $cause): void
    {
        $failHandler->handle($cause, $message);
    }
}
