<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;
use PHP\Kafka\Exceptions\DontCommitException;

class DontCommitFailHandler implements FailHandler
{
    /**
     * @param  Throwable    $cause
     * @param  Message|null $message
     * @throws DontCommitException
     */
    public function handle(Throwable $cause, ?Message $message = null): void
    {
        throw new DontCommitException('Will not commit', 1, $cause);
    }
}
