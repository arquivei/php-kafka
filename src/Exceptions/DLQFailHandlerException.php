<?php

namespace PHP\Kafka\Exceptions;

use Exception;
use Throwable;

class DLQFailHandlerException extends Exception
{

    /**
     * DLQFailHandlerException constructor.
     */
    public function __construct(
        string $message,
        Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
