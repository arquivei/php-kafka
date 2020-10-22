<?php

declare(strict_types=1);

namespace PHP\Kafka\Decoder;

class NoDecoding implements MessageDecoder
{
    public function decode(string $rawMessage): string
    {
        return $rawMessage;
    }
}
