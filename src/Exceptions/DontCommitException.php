<?php


namespace PHP\Kafka\Exceptions;

use Throwable;

class DontCommitException extends \Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}