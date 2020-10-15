<?php

declare(strict_types=1);

namespace Tests\Unit\Commit;

use Exception;
use PHP\Kafka\Commit\Committer;

class FailingCommitter implements Committer
{
    private $timesToFail;
    private $failure;
    private $timesTriedToCommitMessage = 0;
    private $timesTriedToCommitDlq = 0;
    private $commitCount = 0;

    public function __construct(Exception $failure, int $timesToFail)
    {
        $this->failure = $failure;
        $this->timesToFail = $timesToFail;
    }

    public function commitMessage(): void
    {
        $this->timesTriedToCommitMessage++;
        $this->commit();
    }

    public function commitFailure(): void
    {
        $this->timesTriedToCommitDlq++;
        $this->commit();
    }

    private function commit(): void
    {
        $this->commitCount++;
        if ($this->commitCount > $this->timesToFail) {
            $this->commitCount = 0;
            return;
        }

        throw $this->failure;
    }

    public function getTimesTriedToCommitMessage(): int
    {
        return $this->timesTriedToCommitMessage;
    }

    public function getTimesTriedToCommitDlq(): int
    {
        return $this->timesTriedToCommitDlq;
    }
}
