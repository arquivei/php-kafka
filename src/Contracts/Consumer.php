<?php

namespace Kafka\Consumer\Contracts;

abstract class Consumer
{
    public abstract function handle(string $message): void;

    public function failed(string $message, string $topic, \Throwable $exception): void
    {
        throw $exception;
    }

    public function producerKey(string $message): ?string
    {
        return null;
    }
}
