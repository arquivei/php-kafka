<?php

namespace Tests\Unit\Config;

class MyConsumer
{
    public function __invoke(string $message): void
    {
        var_dump($message);
    }
}
