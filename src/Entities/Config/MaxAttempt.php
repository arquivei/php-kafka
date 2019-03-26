<?php

namespace Kafka\Consumer\Entities\Config;

class MaxAttempt
{
    private $value;

    public function __construct(?int $value)
    {
        $this->value = $value;
    }

    public function hasMaxAttempts(): bool
    {
        return !is_null($this->value);
    }

    public function hasReachedMaxAttempts(int $attempts): bool
    {
        return $attempts >= $this->value;
    }
}
