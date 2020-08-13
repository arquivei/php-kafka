<?php

namespace PHP\Kafka\Exceptions;

use Exception;
use Throwable;

class InvalidConsumerException extends Exception
{
    public function __construct(
        string $message = 'Invalid consumer',
        Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
