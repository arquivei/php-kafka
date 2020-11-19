<?php

declare(strict_types=1);

namespace PHP\Kafka\Listener;

use PHP\Kafka\Exceptions\CannotDecodeMessageException;

class NullListener implements ConsumerListener
{
    public function messageDecodingFailed(CannotDecodeMessageException $error): void
    {
    }
}
