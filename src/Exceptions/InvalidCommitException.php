<?php

namespace Kafka\Consumer\Exceptions;

use Exception;
use Throwable;

class InvalidCommitException extends Exception
{
    public function __construct(
        $message = 'Invalid commit',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
