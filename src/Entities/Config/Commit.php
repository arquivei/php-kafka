<?php

namespace Kafka\Consumer\Entities\Config;

class Commit
{
    private $value;

    public function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function isCommitInBatch(): bool
    {
        return is_int($this->value) && $this->value > 1;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
}
