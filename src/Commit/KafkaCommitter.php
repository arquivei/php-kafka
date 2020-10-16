<?php

declare(strict_types=1);

namespace PHP\Kafka\Commit;

use RdKafka\KafkaConsumer;

/**
 * Kafka committer
 *
 * It commits the offsets of the consumer
 */
class KafkaCommitter implements Committer
{
    private KafkaConsumer $consumer;

    public function __construct(KafkaConsumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function commitMessage(): void
    {
        $this->consumer->commit();
    }

    public function commitFailure(): void
    {
        $this->consumer->commit();
    }
}
