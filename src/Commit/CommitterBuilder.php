<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

use PHP\Kafka\MessageCounter;
use RdKafka\KafkaConsumer;

class CommitterBuilder
{
    private $committer;

    public static function withConsumer(KafkaConsumer $consumer): self
    {
        return (new self())
            ->withKafkaCommitter($consumer);
    }

    public function andRetry(Sleeper $sleeper, int $maximumRetries): self
    {
        $this->committer = new RetryableCommitter(
            $this->committer,
            $sleeper,
            $maximumRetries
        );
        return $this;
    }

    public function committingInBatches(MessageCounter $messageCounter, int $batchSize): self
    {
        $this->committer = new BatchCommitter(
            $this->committer,
            $messageCounter,
            $batchSize
        );
        return $this;
    }

    public function build(): Committer
    {
        return $this->committer;
    }

    private function withKafkaCommitter(KafkaConsumer $consumer): self
    {
        $this->committer = new KafkaCommitter($consumer);
        return $this;
    }
}
