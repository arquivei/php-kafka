<?php

namespace PHP\Kafka\Exceptions;

use Exception;
use Throwable;

class KafkaConsumerException extends Exception
{
    public function __construct(
        string $message = 'Error consuming kafka topic',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
