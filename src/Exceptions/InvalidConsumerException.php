<?php

namespace PHP\Kafka\Exceptions;

use Throwable;

class InvalidConsumerException extends \Exception
{
    public function __construct(
        string $message = 'Invalid consumer',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
