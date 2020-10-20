<?php

declare(strict_types=1);

namespace PHP\Kafka\Decoder;

use JsonException;
use PHP\Kafka\Exceptions\CannotDecodeMessageException;
use stdClass;

class JsonDecoder implements MessageDecoder
{
    public function decode(string $rawMessage): stdClass
    {
        try {
            return json_decode($rawMessage, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new CannotDecodeMessageException($rawMessage, 'json', $exception);
        }
    }
}
