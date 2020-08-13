<?php

namespace Tests\FailHandler;

use RdKafka\Message;
use PHPUnit\Framework\TestCase;
use PHP\Kafka\FailHandler\CommitAlwaysFailHandler;

class CommitAlwaysFailHandlerTest extends TestCase
{

    /**
     * @doesNotPerformAssertions
     */
    public function testCommitAlwaysFailHandler()
    {
        $commitAlwaysFailHandler = new CommitAlwaysFailHandler();
        $commitAlwaysFailHandler->handle(new \Exception(), new Message());
    }
}
