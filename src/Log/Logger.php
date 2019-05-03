<?php

namespace Kafka\Consumer\Log;

class Logger
{
    public function error(?int $messageId, int $attempts, \Throwable $exception): void
    {
        $error = json_encode([
            'throwable' => $exception,
            'attempt' => $attempts,
            'time' => date('Y-m-d H:i:s'),
        ]);
        print "[PHP-KAFKA-CONSUMER-ERROR][$messageId]: $error" . PHP_EOL;
    }
}
