<?php

namespace Kafka\Consumer\Exceptions;

use Throwable;

class InvalidConsumerException extends \Exception
{
    public function __construct(
        $message = 'Invalid consumer',
        $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
