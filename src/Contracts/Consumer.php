<?php

namespace Kafka\Consumer\Contracts;

interface Consumer
{
    public function handle(): void;
}
