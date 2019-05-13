<?php

namespace Kafka\Consumer\Entities\Config;

class Sleep
{
    private $time;

    public function __construct(?int $time)
    {
        $this->time = $time;
    }

    public function waiting(): void
    {
        if (!is_null($this->time)) {
            usleep($this->time * 1000);
        }
    }
}
