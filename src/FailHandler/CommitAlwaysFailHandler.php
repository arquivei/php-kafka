<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;

/**
 * Class DefaultFailHandler
 * @package PHP\Kafka\FailHandler
 *
 * Do nothing when error
 */
class CommitAlwaysFailHandler implements FailHandler
{

    public function __construct()
    {
    }

    /**
     * @param Throwable $cause
     * @param Message|null $message
     * @throws Throwable
     */
    public function handle(Throwable $cause, ?Message $message = null): void
    {
    }
}