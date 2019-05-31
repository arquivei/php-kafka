<?php

namespace Kafka\Consumer\Contracts;

interface Consumer
{
    public function handle(string $message): void;
    public function producerKey(string $message): ?string;
}
