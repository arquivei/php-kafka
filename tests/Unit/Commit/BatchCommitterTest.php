<?php

declare(strict_types=1);

namespace Tests\Unit\Commit;

use PHP\Kafka\Commit\BatchCommitter;
use PHP\Kafka\Commit\Committer;
use PHP\Kafka\MessageCounter;
use PHPUnit\Framework\TestCase;

class BatchCommitterTest extends TestCase
{
    public function testShouldCommitMessageOnlyAfterTheBatchSizeIsReached()
    {
        $committer = $this->createMock(Committer::class);
        $committer
            ->expects($this->exactly(2))
            ->method('commitMessage');

        $batchSize = 3;
        $messageCounter = new MessageCounter(42);
        $batchCommitter = new BatchCommitter($committer, $messageCounter, $batchSize);

        for ($i = 0; $i < 7; $i++) {
            $batchCommitter->commitMessage();
        }
    }

    public function testShouldAlwaysCommitDlq()
    {
        $committer = $this->createMock(Committer::class);
        $committer
            ->expects($this->exactly(2))
            ->method('commitFailure');

        $batchSize = 3;
        $messageCounter = new MessageCounter(42);
        $batchCommitter = new BatchCommitter($committer, $messageCounter, $batchSize);

        $batchCommitter->commitFailure();
        $batchCommitter->commitFailure();
    }
}
