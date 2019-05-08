<?php

namespace Kafka\Consumer\Log;

class Logger
{
    public function error(?int $messageId, int $attempts, \Throwable $exception): void
    {
        $error = json_encode([
            'message' => 'PHP-KAFKA-CONSUMER-ERROR',
            'message_id' => $messageId,
            'throwable' => $exception,
            'attempt' => $attempts,
            'time' => date('Y-m-d H:i:s'),
        ]);
        print $error . PHP_EOL;
    }
}
