<?php

declare(strict_types=1);

namespace PHP\Kafka\Exceptions;

use RuntimeException;
use Throwable;

class CannotDecodeMessageException extends RuntimeException
{
    private string $rawMessage;

    public function __construct(string $rawMessage, string $expectedFormat, Throwable $previous = null)
    {
        $message = sprintf('Failed to decode message as \'%s\'.', $expectedFormat);
        parent::__construct($message, 0, $previous);
        $this->rawMessage = $rawMessage;
    }

    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }
}
