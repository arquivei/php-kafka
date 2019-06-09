<?php

namespace Kafka\Consumer\Contracts;

interface Consumer
{
    public function handle(string $message): void;
    public function failed(string $message, string $queue, \Throwable $exception): void;
    public function producerKey(string $message): ?string;
}
