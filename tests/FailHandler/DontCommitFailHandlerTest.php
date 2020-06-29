<?php

namespace Tests\FailHandler;

use RdKafka\Message;
use PHPUnit\Framework\TestCase;
use PHP\Kafka\Exceptions\DontCommitException;
use PHP\Kafka\FailHandler\DontCommitFailHandler;

class DontCommitFailHandlerTest extends TestCase
{

    public function testDontCommitFailHandler()
    {
        $this->expectException(DontCommitException::class);
        $dontCommitFailHandler = new DontCommitFailHandler();
        $dontCommitFailHandler->handle(new \Exception('error'), new Message());
    }
}
