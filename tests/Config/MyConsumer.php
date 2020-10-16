<?php

namespace Tests\Config;

use PHP\Kafka\Contracts\Consumer;
use RdKafka\Message;

class MyConsumer extends Consumer
{

    /**
     * MyConsumer constructor.
     */
    public function __construct()
    {
    }

    public function handle(Message $message): void
    {
        // TODO: Implement handle() method.
        var_dump($message);
    }
}
