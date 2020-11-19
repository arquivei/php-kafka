<?php

declare(strict_types=1);

namespace PHP\Kafka\Listener;

use PHP\Kafka\Exceptions\CannotDecodeMessageException;

interface ConsumerListener
{
    /**
     * Called when a message cannot be decoded
     *
     * @param CannotDecodeMessageException $error The exception thrown during decoding.
     * The previous exception should contain the root error.
     */
    public function messageDecodingFailed(CannotDecodeMessageException $error): void;
}
