<?php

namespace Kafka\Consumer\Exceptions;

use Throwable;

class KafkaConsumerException extends \Exception
{
    public function __construct(
        string $message = 'Error in consume kafka topic',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
