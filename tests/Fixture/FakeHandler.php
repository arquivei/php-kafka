<?php

declare(strict_types=1);

namespace Tests\Fixture;

class FakeHandler
{
    private $lastMessage = null;

    public function __invoke($message): void
    {
        $this->lastMessage = $message;
    }

    public function lastMessage()
    {
        return $this->lastMessage;
    }
}
