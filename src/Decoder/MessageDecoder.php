<?php

declare(strict_types=1);

namespace PHP\Kafka\Decoder;

use PHP\Kafka\Exceptions\CannotDecodeMessageException;

interface MessageDecoder
{
    /**
     * Decodes raw message into another representation expected by the handler
     *
     * @param string $rawMessage
     *
     * @return mixed
     *
     * @throws CannotDecodeMessageException When the message cannot be decoded
     */
    public function decode(string $rawMessage);
}
