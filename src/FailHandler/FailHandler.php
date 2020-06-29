<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;

interface FailHandler
{
    public function handle(Throwable $cause, ?Message $message): void;
}