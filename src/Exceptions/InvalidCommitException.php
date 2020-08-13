<?php

namespace PHP\Kafka\Exceptions;

use Exception;
use Throwable;

class InvalidCommitException extends Exception
{
    public function __construct(
        string $message = 'Invalid commit',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
