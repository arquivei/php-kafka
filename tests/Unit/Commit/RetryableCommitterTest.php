<?php

declare(strict_types=1);

namespace Tests\Unit\Commit;

use PHP\Kafka\Commit\RetryableCommitter;
use PHPUnit\Framework\TestCase;
use RdKafka\Exception as RdKafkaException;

class RetryableCommitterTest extends TestCase
{
    public function testShouldRetryToCommit()
    {
        $exception = new RdKafkaException("Something went wrong", RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT);
        $failingCommitter = new FailingCommitter($exception, 3);
        $retryableCommitter = new RetryableCommitter($failingCommitter, new FakeSleeper());

        $retryableCommitter->commitMessage();
        $retryableCommitter->commitFailure();

        $this->assertEquals(4, $failingCommitter->getTimesTriedToCommitMessage());
        $this->assertEquals(4, $failingCommitter->getTimesTriedToCommitDlq());
    }

    public function testShouldRetryOnlyUpToTheMaximumNumberOfRetries()
    {
        $expectedException = new RdKafkaException("Something went wrong", RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT);
        $failingCommitter = new FailingCommitter($expectedException, 99);
        $retryableCommitter = new RetryableCommitter($failingCommitter, new FakeSleeper(), 4);

        $commitMessageException = null;
        try {
            $retryableCommitter->commitMessage();
        } catch (RdKafkaException $exception) {
            $commitMessageException = $exception;
        }

        $commitDlqException = null;
        try {
            $retryableCommitter->commitFailure();
        } catch (RdKafkaException $exception) {
            $commitDlqException = $exception;
        }

        // first execution + 4 retries = 5 executions
        $this->assertEquals(5, $failingCommitter->getTimesTriedToCommitMessage());
        $this->assertSame($expectedException, $commitMessageException);

        $this->assertEquals(5, $failingCommitter->getTimesTriedToCommitDlq());
        $this->assertSame($expectedException, $commitDlqException);
    }

    public function testShouldProgressivelyWaitForTheNextRetry()
    {
        $expectedException = new RdKafkaException("Something went wrong", RD_KAFKA_RESP_ERR_REQUEST_TIMED_OUT);

        $sleeper = new FakeSleeper();
        $failingCommitter = new FailingCommitter($expectedException, 99);
        $retryableCommitter = new RetryableCommitter($failingCommitter, $sleeper, 6);

        try {
            $retryableCommitter->commitMessage();
        } catch (RdKafkaException $exception) {
        }

        $expectedSleeps = [1e6, 2e6, 4e6, 8e6, 16e6, 32e6];
        $this->assertEquals($expectedSleeps, $sleeper->getSleeps());
    }
}
