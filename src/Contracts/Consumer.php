<?php

namespace Kafka\Consumer\Contracts;

interface Consumer
{
    public function handle(string $message): void;
}
