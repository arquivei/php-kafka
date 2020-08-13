<?php

namespace PHP\Kafka\FailHandler;

use Throwable;
use RdKafka\Message;

/**
 * Class CommitAlwaysFailHandler
 *
 * @package PHP\Kafka\FailHandler
 *
 * Do nothing when error
 */
class CommitAlwaysFailHandler implements FailHandler
{

    /**
     * @param Throwable    $cause
     * @param Message|null $message
     */
    public function handle(Throwable $cause, ?Message $message = null): void
    {
    }
}
