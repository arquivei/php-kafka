<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

use PHP\Kafka\MessageCounter;

/**
 * Decorates a committer with batch capabilities.
 *
 * This will commit the offsets in batches, instead of for every single message received. This has an improvement on
 * performance.
 */
class BatchCommitter implements Committer
{
    private int $commits = 0;
    private Committer $committer;
    private MessageCounter $messageCounter;
    private int $batchSize;

    public function __construct(Committer $committer, MessageCounter $messageCounter, int $batchSize)
    {
        $this->committer = $committer;
        $this->messageCounter = $messageCounter;
        $this->batchSize = $batchSize;
    }

    public function commitMessage(): void
    {
        $this->commits++;
        if ($this->isMaxMessage() || $this->commits >= $this->batchSize) {
            $this->committer->commitMessage();
            $this->commits = 0;
        }
    }

    private function isMaxMessage(): bool
    {
        return $this->messageCounter->isMaxMessage();
    }

    public function commitFailure(): void
    {
        $this->committer->commitFailure();
        $this->commits = 0;
    }
}
