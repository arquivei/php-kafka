<?php

declare(strict_types=1);

namespace PHP\Kafka\Exceptions;

class DLQFailHandlerException extends \Exception
{
    public function __construct(string $message, \Throwable $previous)
    {
        parent::__construct($message, 0, $previous);
    }
}
