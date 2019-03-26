<?php

namespace Kafka\Consumer\Entities\Config;

class Consumer
{
    private $default;
    private $customs;
    private $onlyDefault;

    public function __construct(?string $default, array $customs, bool $onlyDefault = false)
    {
        $this->default = $default;
        $this->customs = $customs;
        $this->onlyDefault = $onlyDefault;
    }

    public function getDefault(): ?string
    {
        return $this->default;
    }

    public function getCustoms(): array
    {
        return $this->customs;
    }

    public function isOnlyDefault(): bool
    {
        return $this->onlyDefault;
    }

    public function hasConsumerDefault(): bool
    {
        return !is_null($this->default) && strlen($this->default) > 1;
    }
}
